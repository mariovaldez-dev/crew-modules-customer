<?php

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Repositories\IAuthRepository;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuthRepository implements IAuthRepository
{
    public function authenticate(string $username, string $password, string $platform = 'WEB'): ?User
    {
        Log::info('[AuthRepository] Iniciando autenticación mediante SP proc_pdm_login', ['usuario' => $username, 'platform' => $platform]);

        try {
            DB::connection('localDB')->statement("SET ANSI_NULLS ON;");
            DB::connection('localDB')->statement("SET ANSI_WARNINGS ON;");

            $results = DB::connection('localDB')->select(
                "EXEC proc_pdm_login @Usuario = :usuario, @Contrasena = :contrasena",
                [
                    'usuario' => $username,
                    'contrasena' => $password,
                ]
            );

            Log::info('[AuthRepository] Consulta SP proc_pdm_login completada', ['total_filas' => count($results)]);

            if (!empty($results)) {
                $row = (object) $results[0];
                $estado = (int) ($row->estado ?? -1);
                $mensaje = (string) ($row->mensaje ?? '');

                Log::info('[AuthRepository] Respuesta de proc_pdm_login', [
                    'usuario' => $username,
                    'estado' => $estado,
                    'mensaje' => $mensaje,
                    'codigoAsesor' => $row->codigoAsesor ?? null,
                    'nombreAsesor' => $row->nombreAsesor ?? null,
                    'zonaAsesor' => $row->zonaAsesor ?? null,
                    'rolAsesor' => $row->rolAsesor ?? null,
                ]);

                if (in_array($estado, [0, 1, 200], true) || (!empty($row->codigoAsesor) && $estado >= 0)) {
                    $user = new User(
                        id: (int) ($row->codigoAsesor ?? 0),
                        name: (string) ($row->nombreAsesor ?? $username),
                        email: str_contains($username, '@') ? $username : $username . '@impulsora.com',
                        rol: (string) ($row->rolAsesor ?? ''),
                        activo: true,
                        sessionId: 'sess_' . md5($username . time()),
                        zona: !empty($row->zonaAsesor) ? (string) $row->zonaAsesor : null
                    );

                    Log::info('[AuthRepository] Usuario autenticado con éxito', ['user_id' => $user->id, 'rol' => $user->rol]);
                    return $user;
                } else {
                    Log::warning('[AuthRepository] proc_pdm_login devolvió credenciales inválidas', [
                        'usuario' => $username,
                        'estado' => $estado,
                        'mensaje' => $mensaje
                    ]);
                    return null;
                }
            } else {
                Log::warning('[AuthRepository] SP proc_pdm_login devolvió 0 filas');
                return null;
            }
        } catch (\Throwable $e) {
            Log::error('[AuthRepository] Excepción al ejecutar proc_pdm_login', [
                'usuario' => $username,
                'message' => $e->getMessage()
            ]);
            return null;
        }
    }

    public function logout(string $sessionId): bool
    {
        Log::info("Cierre de sesión de usuario", ['sessionId' => $sessionId]);
        return true;
    }

    public function logAttempt(string $username, bool $success, string $ip, string $platform = 'WEB'): void
    {
        // RN-05: Registrar cada intento
        Log::info('Auditoría de Intento Login', [
            'username' => $username,
            'success' => $success,
            'ip' => $ip,
            'platform' => $platform,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
