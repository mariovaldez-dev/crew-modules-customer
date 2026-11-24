<?php




namespace App\UI\Http\Middleware\ValidarSesionImpulsora;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ValidarSesionImpulsora
{
    public function handle(Request $request, Closure $next)
    {
        // No afectar los accesos normales de Maniobras
        if (
            $request->session()->get('tipo_login')
            !== 'IMPULSORA_SSO'
        ) {
            return $next($request);
        }

        $token = $request->session()->get('session_id_app');
        $codigo = $request->session()->get('session_code_app');

        $activa = false;

        if ($token && $codigo) {
            try {
                $activa = DB::connection('loginDB')
                    ->table('DEVICESESSIONS')
                    ->where('Code', $codigo)
                    ->where('U_SessionId', $token)
                    ->where(
                        'U_Sistema',
                        'IMPULSORA_DIGITAL_MANIOBRAS'
                    )
                    ->where('U_TipoLogin', 'IMPULSORA_SSO')
                    ->where('U_Activo', 'Y')
                    ->where('U_Revocado', 'N')
                    ->exists();

            } catch (\Throwable $e) {
                Log::error('[Maniobras SSO] Error validando sesión', [
                    'mensaje' => $e->getMessage()
                ]);

                abort(503, 'No se pudo validar la sesión externa.');
            }
        }

        if (!$activa) {

            Log::warning('[Maniobras SSO] Sesión revocada', [
                'codigo_sesion' => $codigo
            ]);

            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')
                ->with(
                    'error',
                    'Tu sesión de Impulsora Digital ha finalizado.'
                );
        }

        return $next($request);
    }
}
