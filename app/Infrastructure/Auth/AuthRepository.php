<?php

namespace App\Infrastructure\Auth;

use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Repositories\IAuthRepository;
use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AuthRepository implements IAuthRepository
{
    public function authenticate(string $username, string $password, string $platform = 'WEB'): ?User
    {
        // RQM-06: Mockup de login con usuarios fake para pruebas de roles y scoping
        $fakeUsers = [
            'admin' => [
                'id' => 1,
                'name' => 'Administrador Maniobras (Fake)',
                'email' => 'admin@fake.com',
                'rol' => 'AM',
                'activo' => true
            ],
            'coord_norte' => [
                'id' => 2,
                'name' => 'Coordinadora Norte (Fake)',
                'email' => 'norte@fake.com',
                'rol' => 'CO',
                'activo' => true
            ],
            'coord_sur' => [
                'id' => 3,
                'name' => 'Coordinadora Sur (Fake)',
                'email' => 'sur@fake.com',
                'rol' => 'CO',
                'activo' => true
            ],
            'inactivo' => [
                'id' => 999,
                'name' => 'Usuario Inactivo (Fake)',
                'email' => 'inactivo@fake.com',
                'rol' => 'CO',
                'activo' => false
            ]
        ];

        $lowerUsername = strtolower($username);
        if (array_key_exists($lowerUsername, $fakeUsers)) {
            $fakeData = $fakeUsers[$lowerUsername];
            Log::info("Autenticación simulada con usuario fake", ['usuario' => $username]);
            
            return new User(
                id: $fakeData['id'],
                name: $fakeData['name'],
                email: $fakeData['email'],
                rol: $fakeData['rol'],
                activo: $fakeData['activo'],
                sessionId: 'fake-session-' . $username
            );
        }

        try {
            $apiUrl = config('services.auth_api.url');
            
            if (!$apiUrl) {
                Log::error("AUTH_API_URL no configurada en .env");
                return null;
            }

            $loginUrl = rtrim($apiUrl) . 'auth/login';
            Log::info("Intento de autenticación vía API externa", [
                'usuario' => $username, 
                'url' => $loginUrl,
                'plataforma' => $platform
            ]);

            $userAgent = request()->header('User-Agent', 'Unknown');
            $deviceName = $this->inferDeviceName($userAgent);
            $deviceId = request()->header('X-Device-ID') ?? md5($userAgent . request()->ip());

            Log::info('Informaciòn de inicio de sesion: ', [ 'userAgent'=>$userAgent, 'deviceId'=> $deviceId , ''=> $platform ]);

            $response = Http::timeout(10)->post($loginUrl, [
                'correo' => $username,
                'password' => $password,
                'deviceId' => $deviceId,
                'deviceName' => $deviceName,
                'plataforma' => $platform,
                'versionApp' => config('app.version', '1.0.0')
            ]);

            if ($response->failed()) {
                Log::warning("Fallo en la respuesta de la API de autenticación", [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'usuario' => $username
                ]);
                return null;
            }

            $data = $response->json();

            if (!($data['success'] ?? false) || !isset($data['user'])) {
                Log::warning("Login fallido o respuesta sin datos de usuario", ['data' => $data]);
                return null;
            }

            $userData = $data['user'];

            return new User(
                id: (int) ($userData['CodigoAgente'] ?? $userData['Code'] ?? 0),
                name: $userData['Nombre'] ?? 'Usuario',
                email: $userData['Correo'] ?? $username,
                rol: $userData['tipoAgente'] ?? '',
                activo: true,
                sessionId: $data['sessionId'] ?? null
            );

        } catch (Exception $e) {
            Log::error('Excepción en AuthRepository@authenticate via API', [
                'usuario' => $username,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function logout(string $sessionId): bool
    {
        try {
            $apiUrl = config('services.auth_api.url');
            if (!$apiUrl) return false;

            $logoutUrl = rtrim($apiUrl) . 'auth/logout';
            
            $response = Http::timeout(10)->post($logoutUrl, [
                'sessionId' => $sessionId
            ]);

            Log::info("Cierre de sesión en API externa", [
                'sessionId' => $sessionId,
                'status' => $response->status(),
                'success' => $response->successful()
            ]);

            return $response->successful();

        } catch (Exception $e) {
            Log::error('Error en AuthRepository@logout', [
                'sessionId' => $sessionId,
                'message' => $e->getMessage(),
            ]);
            return false;
        }
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

    private function inferDeviceName(string $userAgent): string
    {
        if (str_contains($userAgent, 'iPhone')) return 'iPhone';
        if (str_contains($userAgent, 'iPad')) return 'iPad';
        if (str_contains($userAgent, 'Android')) return 'Android Device';
        if (str_contains($userAgent, 'Macintosh')) return 'Mac';
        if (str_contains($userAgent, 'Windows')) return 'Windows PC';
        if (str_contains($userAgent, 'Linux')) return 'Linux PC';

        return 'Web Browser';
    }
}
