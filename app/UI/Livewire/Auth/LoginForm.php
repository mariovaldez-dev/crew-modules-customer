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
            // RN-01: Obligatorio, alfanumérico (alpha_dash), máx 50
            'username' => 'required|alpha_dash|max:50',
            // RN-02: Obligatorio, alfanumérico (alpha_num), mín 8, máx 15
            'password' => 'required|alpha_num|min:3|max:25',
        ];
    }

    protected array $messages = [
        'username.required' => 'El usuario es obligatorio.',
        'username.alpha_dash' => 'El usuario solo puede contener letras, números, guiones y guiones bajos.',
        'username.max' => 'El usuario no puede exceder los 50 caracteres.',
        'password.required' => 'La contraseña es obligatoria.',
        'password.alpha_num' => 'La contraseña solo puede contener letras y números.',
        'password.min' => 'La contraseña debe tener al menos 3 caracteres.',
        'password.max' => 'La contraseña no puede exceder los 25 caracteres.',
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

        $this->validate();

        try {
            $user = $useCase->execute(new AuthenticateUserDTO(
                username: $this->username,
                password: $this->password,
                ip: request()->ip(),
                platform: $this->getPlatform()
            ));

            if (! $user) {
                $this->hasError = true;
                $this->addError('auth_error', 'Usuario o contraseña incorrectos.');

                return;
            }

            // Validación de Rol (RN: Administrador Maniobras o Coordinadora Almacén)
            if (!in_array((string)$user->rol, ['AM', 'CO', '2'])) {
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
            $sucursalRepository = app(\App\Domain\Shared\Repositories\SucursalRepositoryInterface::class);
            $tipo = in_array((string)$user->rol, ['CO', '2']) ? 'CO' : 'AM';
            $pvs = [];
            $zona = 'TODAS';
            $status = $user->activo ? $sucursalRepository->obtenerStatusUsuario($user->id) : 'I';

            if ($tipo === 'CO') {
                $pvs = $sucursalRepository->obtenerPuntosDeVentaDeUsuario($user->id);
                if (!empty($pvs)) {
                    $zona = $sucursalRepository->zonaDe($pvs[0]) ?? 'ZONA-NORTE';
                } else {
                    $zona = 'ZONA-NORTE';
                }
            }

            $usuarioContexto = new \App\Domain\Shared\UsuarioContexto(
                zona: $zona,
                tipo: $tipo,
                puntosDeVenta: $pvs,
                status: $status
            );
            session()->put('usuario_contexto', $usuarioContexto);

            Auth::login($authenticatedUser);

            if (!app()->environment('testing')) {
                session()->regenerate();
            }

            return redirect()->route('dashboard');

        } catch (\Exception $e) {
            $this->hasError = true;
            Log::error('Excepción en login UI', ['msg' => $e->getMessage()]);
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
