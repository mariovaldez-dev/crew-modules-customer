<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Shared\UsuarioContexto;
use App\Infrastructure\Auth\Models\AuthenticatedUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AccesoAppController
{
    public function accesoApp(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | 1. Recuperar token enviado por Impulsora Digital
        |--------------------------------------------------------------------------
        */

        $token = $request->post('token');

        if (
            !is_string($token) ||
            strlen($token) !== 80
        ) {
            return redirect()->route('login')
                ->with('error', 'Token SSO no proporcionado o inválido.');
        }

        try {

            /*
            |--------------------------------------------------------------------------
            | 2. Validar token de Maniobras en DEVICESESSIONS
            |--------------------------------------------------------------------------
            */

            $sesion = DB::connection('loginDB')
                ->table('DEVICESESSIONS')
                ->where('U_SessionId', $token)
                ->where(
                    'U_Sistema',
                    'IMPULSORA_DIGITAL_MANIOBRAS'
                )
                ->where('U_TipoLogin', 'IMPULSORA_SSO')
                ->where('U_DeviceId', 'IMPULSORA_DIGITAL')
                ->where('U_Activo', 'Y')
                ->where('U_Revocado', 'N')
                ->first();

            if (!$sesion) {

                Log::warning('[Maniobras SSO] Token inválido');

                return redirect()->route('login')
                    ->with(
                        'error',
                        'La sesión de Impulsora Digital no es válida.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | 3. Consultar el agente en SAP
            |--------------------------------------------------------------------------
            */

            $codigoAgente = $sesion->U_IdAgente;

            $agente = DB::connection('sapImpulsoraDB')
                ->table('@AGENTES_VENTAS')
                ->where('Code', $codigoAgente)
                ->first();

            if (!$agente) {

                Log::warning('[Maniobras SSO] Agente no encontrado', [
                    'codigo_agente' => $codigoAgente
                ]);

                return redirect()->route('login')
                    ->with(
                        'error',
                        'No se encontró el agente de la sesión.'
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | 4. Obtener perfil autorizado para la oficina
            |--------------------------------------------------------------------------
            */

            $perfil = config(
                'maniobras_sso.agentes.' . $codigoAgente
            );

            if (
                !is_array($perfil) ||
                ($perfil['activo'] ?? false) !== true
            ) {
                return redirect()->route('login')
                    ->with('error', 'Usuario sin permiso para Maniobras.');
            }

            /*
            |--------------------------------------------------------------------------
            | 5. Identificar al usuario original de Impulsora Digital
            |--------------------------------------------------------------------------
            */

            $origen = (string) ($sesion->U_DeviceName ?? '');

            $correoOrigen = str_starts_with($origen, 'SSO:')
                ? strtolower(trim(substr($origen, 4)))
                : '';

            $correoPermitido = strtolower(
                trim((string) $perfil['email'])
            );

            if (
                $correoOrigen === '' ||
                !hash_equals($correoPermitido, $correoOrigen)
            ) {
                Log::warning('[Maniobras SSO] Identidad no autorizada', [
                    'codigo_agente' => $codigoAgente
                ]);

                return redirect()->route('login')
                    ->with(
                        'error',
                        'El usuario no corresponde al agente autorizado.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | 6. Verificar que la oficina corresponda a la zona
            |--------------------------------------------------------------------------
            */

            $zonaOficina = strtoupper(
                trim((string) ($agente->U_Zona ?? ''))
            );

            if ($zonaOficina !== $perfil['zona']) {
                return redirect()->route('login')
                    ->with('error', 'La zona del agente no corresponde.');
            }

            /*
            |--------------------------------------------------------------------------
            | 7. Consultar al usuario real de Maniobras en SAP
            |--------------------------------------------------------------------------
            */

            $codigoUsuario = str_pad(
                (string) $perfil['id'],
                3,
                '0',
                STR_PAD_LEFT
            );

            $cuenta = DB::connection('maniobras')->selectOne(
                "
                SELECT TOP 1
                    age.Code,
                    age.Name,
                    age.U_usuario,
                    age.U_Tipo,
                    age.U_serieSucursal
                FROM SAP.SBO_Impulsora_PROD.dbo.[@USUARIOS_PUNTOVENTA] AS age
                WHERE age.Code = ?
                AND LOWER(LTRIM(RTRIM(age.U_usuario))) = ?
                AND age.U_status = 'A'
                AND age.U_Tipo IN ('CA', 'CO', 'AM')
                AND age.U_serieSucursal = ?
                ",
                [
                    $codigoUsuario,
                    $correoOrigen,
                    $zonaOficina
                ]
            );

            if (!$cuenta) {
                Log::warning('[Maniobras SSO] Usuario SAP no válido', [
                    'codigo_agente' => $codigoAgente,
                    'codigo_usuario' => $codigoUsuario
                ]);

                return redirect()->route('login')
                    ->with(
                        'error',
                        'No se encontró una cuenta activa de Maniobras.'
                    );
            }

            /*
            |--------------------------------------------------------------------------
            | 8. Preparar identidad con los datos reales de SAP
            |--------------------------------------------------------------------------
            */

            $userId = (int) $cuenta->Code;

            $rol = strtoupper(trim((string) $cuenta->U_Tipo));
            $rol = $rol === 'AM' ? 'AM' : 'CO';

            if ($rol !== $perfil['rol']) {
                return redirect()->route('login')
                    ->with('error', 'El rol del usuario no coincide.');
            }

            $tipo = $rol;
            $zona = $zonaOficina;
            $nombreAgente = (string) $cuenta->Name;

            Log::info('[Maniobras SSO] Usuario validado', [
                'usuario_id' => $userId,
                'agente' => $codigoAgente,
                'rol' => $rol,
                'zona' => $zona
            ]);


            $authenticatedUser = new AuthenticatedUser([
                'id' => $userId,
                'name' => $nombreAgente,
                'email' => $perfil['email'] ?? '',
                'rol' => $rol,
                'sessionId' => $sesion->U_SessionId,
            ]);

            /*
            |--------------------------------------------------------------------------
            | 7. Construir UsuarioContexto
            |--------------------------------------------------------------------------
            |
            | Mismo objeto utilizado por LoginForm.
            |
            */

            $usuarioContexto = new UsuarioContexto(
                zona: $zona,
                zonaNombre: $perfil['zona_nombre'] ?? '',
                tipo: $tipo,
                puntosDeVenta: [],
                status: 'A'
            );

            /*
            |--------------------------------------------------------------------------
            | 8. Iniciar sesión independiente en Maniobras
            |--------------------------------------------------------------------------
            */

            // Eliminar cualquier sesión anterior del navegador
            $request->session()->invalidate();

            // Usar el mismo guard que LoginForm
            Auth::login($authenticatedUser);

            /*
            |--------------------------------------------------------------------------
            | 9. Guardar las variables de Maniobras
            |--------------------------------------------------------------------------
            */

            session()->put(
                'authenticated_user',
                $authenticatedUser
            );

            session()->put(
                'usuario_contexto',
                $usuarioContexto
            );

            /*
            |--------------------------------------------------------------------------
            | 10. Guardar identificación del acceso SSO
            |--------------------------------------------------------------------------
            */

            session([
                'usuario_logueado' => true,
                'id_agente' => $agente->Code,
                'nombre_agente' => $nombreAgente,

                'tipo_login' => 'IMPULSORA_SSO',

                'session_id_app' => $sesion->U_SessionId,

                'session_code_app' => $sesion->Code,
            ]);

            $request->session()->regenerate();

            /*
            |--------------------------------------------------------------------------
            | 11. Registrar acceso exitoso
            |--------------------------------------------------------------------------
            */

            Log::info('[Maniobras SSO] Acceso exitoso', [
                'usuario_id' => $userId,
                'codigo_agente' => $codigoAgente,
                'rol' => $rol,
                'zona' => $zona,
                'sistema' => $sesion->U_Sistema,
                'codigo_sesion' => $sesion->Code,
            ]);


            Log::info('[Maniobras SSO] DIAGNOSTICO POST', [
                'auth_check' => Auth::guard('web')->check(),
                'auth_id' => Auth::guard('web')->id(),

                'auth_modelo' => Auth::guard('web')->user()
                    ? get_class(Auth::guard('web')->user())
                    : null,

                'provider' => config('auth.guards.web.provider'),

                'provider_modelo' => config(
                    'auth.providers.' .
                    config('auth.guards.web.provider') .
                    '.model'
                ),

                'session_driver' => config('session.driver'),
                'session_cookie' => config('session.cookie'),

                'tipo_login' => $request->session()->get('tipo_login'),
                'usuario_contexto' => $request->session()
                    ->has('usuario_contexto'),
            ]);



            /*
            |--------------------------------------------------------------------------
            | 12. Redireccionar al dashboard
            |--------------------------------------------------------------------------
            */


            try {
                $usuarioRecuperado = Auth::guard('web')
                    ->getProvider()
                    ->retrieveById($userId);

                Log::info('[Maniobras SSO] DIAGNOSTICO PROVIDER', [
                    'usuario_id' => $userId,
                    'provider_recupera_usuario' => $usuarioRecuperado !== null,
                    'clase_recuperada' => $usuarioRecuperado
                        ? get_class($usuarioRecuperado)
                        : null,
                ]);

            } catch (\Throwable $e) {
                Log::warning('[Maniobras SSO] DIAGNOSTICO PROVIDER ERROR', [
                    'tipo_error' => get_class($e),
                    'mensaje' => $e->getMessage(),
                ]);
            }

            Log::info('[Maniobras SSO] REDIRECCION', [
                'url_actual' => $request->fullUrl(),
                'host_actual' => $request->getHttpHost(),
                'app_url' => config('app.url'),
                'session_domain' => config('session.domain'),
                'session_secure' => config('session.secure'),
                'session_path' => config('session.path'),
            ]);


              // return redirect('/sso-diagnostico');
          return redirect()->route('dashboard');

        } catch (Throwable $e) {

            Log::error('[Maniobras SSO] Error de acceso', [
                'mensaje' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->route('login')
                ->with(
                    'error',
                    'Ocurrió un error al iniciar sesión desde Impulsora Digital.'
                );
        }
    }
}
