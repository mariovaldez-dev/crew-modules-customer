<?php

namespace App\UI\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    /**
     * Acceso desde APK por POST utilizando token de sesión.
     * 
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function accesoApp(Request $request)
    {
        $token = $request->input('token');

        if (!$token) {
            return redirect()->route('login')
                ->with('error', 'Token no proporcionado.');
        }

        try {
            // 1. Buscar la sesión en la base de datos de logins (Conexión por defecto sqlsrv)
            $sesion = DB::connection('loginDB')
                ->table('DEVICESESSIONS')
                ->where('U_SessionId', $token)
                ->where('U_Activo', 'Y')
                ->where('U_Revocado', 'N')
                ->first();

            if (!$sesion) {
                return redirect()->route('login')
                    ->with('error', 'Token inválido o expirado.');
            }

            $idAgente = $sesion->U_IdAgente;

            // 2. Buscar el agente en la base de datos SAP
            $agente = DB::connection('sapImpulsoraDB')
                ->table('@AGENTES_VENTAS')
                ->where('Code', $idAgente)
                ->first();

            if (!$agente) {
                return redirect()->route('login')
                    ->with('error', 'No se encontró el agente relacionado con la sesión.');
            }

            if($agente->U_Tipo_Agente !== '2'){
                return redirect()->route('login')
                ->with('error', 'No cuentas con los privilegios para acceder a esta plataforma, contacta al administrador.');
            }

            // 3. Establecer la sesión manual en Laravel
            $request->session()->regenerate();
            
            // Crear el objeto de usuario autenticado para que funcione con el CustomUserProvider y middlewares
            $authUser = new \App\Infrastructure\Auth\Models\AuthenticatedUser([
                'id'        => $sesion->Code ?? $idAgente,
                'name'      => ucwords(strtolower($agente->Name ?? $agente->U_Nombre ?? 'Agente')),
                'email'     => $agente->U_Email ?? 'agente@impulsora.com',
                'rol'       => $agente->U_Tipo_Agente,
                'sessionId' => $sesion->U_SessionId,
            ]);

            // El CustomUserProvider lee de la sesión 'authenticated_user'
            session(['authenticated_user' => $authUser]);

            // Resolver y guardar el UsuarioContexto (RQM-06)
            $tipo = in_array((string)$agente->U_Tipo_Agente, ['CO', '2']) ? 'CO' : 'AM';
            $zona = ($tipo === 'CO') ? 'FA' : 'TODAS';

            $usuarioContexto = new \App\Domain\Shared\UsuarioContexto(
                zona: $zona,
                tipo: $tipo,
                puntosDeVenta: [],
                status: 'A'
            );
            session(['usuario_contexto' => $usuarioContexto]);
            
            // Forzar login en el guard de Laravel
            \Illuminate\Support\Facades\Auth::login($authUser);

            session([
                'usuario_logueado' => true,
                'id_agente'        => $agente->Code,
                'nombre_agente'    => $authUser->name,
                'tipo_login'       => 'APP_TOKEN',
                'session_id_app'   => $sesion->U_SessionId,
            ]);

            // 4. Actualizar última actividad (Conexión por defecto sqlsrv)
            DB::table('DEVICESESSIONS')
                ->where('Code', $sesion->Code)
                ->update([
                    'U_UltimaActividad' => now(),
                ]);

            Log::info("Acceso exitoso desde APK para agente: {$agente->Code}");

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            Log::error("Error en accesoApp: " . $e->getMessage());
            return redirect()->route('login')
                ->with('error', 'Ocurrió un error al procesar el acceso.');
        }
    }
}
