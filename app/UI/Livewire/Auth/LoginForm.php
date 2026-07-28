<?php

namespace App\UI\Livewire\Auth;

use App\Application\Auth\AuthenticateUser\AuthenticateUserDTO;
use App\Application\Auth\AuthenticateUser\AuthenticateUserUseCase;
use App\Infrastructure\Auth\Models\AuthenticatedUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class LoginForm extends Component
{
    public string $username = '';

    public string $password = '';

    public bool $showPassword = false;

    public bool $hasError = false;

    public bool $loading = false;

    protected function rules(): array
    {
        return [
            'username' => 'required|string|max:100',
            'password' => 'required|string|min:1|max:100',
        ];
    }

    protected array $messages = [
        'username.required' => 'El usuario es obligatorio.',
        'username.max' => 'El usuario no puede exceder los 100 caracteres.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.max' => 'La contraseña no puede exceder los 100 caracteres.',
    ];

    /**
     * Se ejecuta cada vez que una propiedad cambia en el cliente.
     */
    public function updated($propertyName)
    {
        // Limpiamos el error específico del campo que se está editando
        $this->resetErrorBag($propertyName);
        
        // También limpiamos el error global de credenciales y el estado de error
        $this->resetErrorBag('auth_error');
        $this->hasError = false;
    }

    public function login(AuthenticateUserUseCase $useCase)
    {
        $this->resetErrorBag();
        $this->hasError = false;

        Log::info('[LoginForm] Intento de login iniciado', [
            'username' => $this->username,
            'ip' => request()->ip()
        ]);

        try {
            $this->validate();
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::warning('[LoginForm] Validación fallida en login', [
                'errors' => $e->errors(),
                'username' => $this->username
            ]);
            throw $e;
        }

        try {
            $user = $useCase->execute(new AuthenticateUserDTO(
                username: $this->username,
                password: $this->password,
                ip: request()->ip(),
                platform: $this->getPlatform()
            ));

            Log::info('[LoginForm] Resultado del UseCase execute', [
                'user_retornado' => !is_null($user),
                'user_id' => $user?->id,
                'user_name' => $user?->name,
                'user_rol' => $user?->rol,
                'user_zona' => $user?->zona,
            ]);

            if (! $user) {
                Log::warning('[LoginForm] Usuario nulo o credenciales inválidas', ['username' => $this->username]);
                $this->hasError = true;
                $this->addError('auth_error', 'Usuario o contraseña incorrectos.');

                return;
            }

            // Validación de Rol (RN: Administrador Maniobras o Coordinadora Almacén)
            $rawRol = strtoupper(trim((string)$user->rol));
            $isCoordinador = in_array($rawRol, ['CO', '2', 'COORDINADOR', 'COORDINADORA', 'ASESOR']);
            $isAdmin = in_array($rawRol, ['AM', '1', 'ADMIN', 'ADMINISTRADOR', 'ADMINISTRADORA']);

            Log::info('[LoginForm] Validación de rol', [
                'rawRol' => $rawRol,
                'isCoordinador' => $isCoordinador,
                'isAdmin' => $isAdmin
            ]);

            if (!$isCoordinador && !$isAdmin) {
                Log::warning('[LoginForm] Rol sin privilegios', ['rawRol' => $rawRol]);
                $this->hasError = true;
                $this->addError('auth_error', 'No cuentas con los privilegios para acceder a esta plataforma, contacta al administrador.');
                return;
            }

            // Mapear a modelo compatible con la sesión de Laravel
            $authenticatedUser = new AuthenticatedUser([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'rol' => $user->rol,
                'sessionId' => $user->sessionId
            ]);

            // Guardar el usuario en la sesión para el CustomUserProvider
            session()->put('authenticated_user', $authenticatedUser);

            // Resolver y guardar el UsuarioContexto (RQM-06)
            $tipo = $isCoordinador ? 'CO' : 'AM';
            $zona = !empty($user->zona) ? $user->zona : ($tipo === 'AM' ? 'TODAS' : 'FA');
            $status = $user->activo ? 'A' : 'I';

            $usuarioContexto = new \App\Domain\Shared\UsuarioContexto(
                zona: $zona,
                tipo: $tipo,
                puntosDeVenta: [],
                status: $status
            );
            session()->put('usuario_contexto', $usuarioContexto);

            Auth::login($authenticatedUser);

            if (!app()->environment('testing')) {
                session()->regenerate();
            }

            Log::info('[LoginForm] Login exitoso, redirigiendo a dashboard', ['user_id' => $user->id]);

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            $this->hasError = true;
            Log::error('[LoginForm] Excepción en login UI', [
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->addError('auth_error', 'Ocurrió un error al procesar el acceso.');
        }
    }

    private function getPlatform(): string
    {
        $userAgent = request()->header('User-Agent', '');

        // Detección de iOS (WebView vs Safari)
        if (str_contains($userAgent, 'iPhone') || str_contains($userAgent, 'iPad') || str_contains($userAgent, 'iPod')) {
            if (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'wv')) {
                return 'IOS_WEB';
            }
            return 'IOS_APP'; // WebView
        }

        // Detección de Android (WebView vs Browser)
        if (str_contains($userAgent, 'Android')) {
            if (str_contains($userAgent, '; wv)')) {
                return 'ANDROID_APP'; // WebView
            }
            return 'ANDROID_WEB';
        }

        return 'WEB'; // Desktop o desconocido
    }

    public function render()
    {
        return view('livewire.auth.login-form');
    }
}
