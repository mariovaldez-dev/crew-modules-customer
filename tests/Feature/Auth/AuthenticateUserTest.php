<?php

namespace Tests\Feature\Auth;

use App\UI\Livewire\Auth\LoginForm;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class AuthenticateUserTest extends TestCase
{
    /** @test */
    public function can_see_login_page()
    {
        $this->get('/login')->assertStatus(200)->assertSeeLivewire('auth.login-form');
    }

    /** @test */
    public function authenticated_user_is_redirected_from_login_page()
    {
        $user = new \App\Infrastructure\Auth\Models\AuthenticatedUser([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
            'rol' => '2',
            'sessionId' => 'fake-session'
        ]);

        session(['authenticated_user' => $user]);

        $this->actingAs($user)
            ->get('/login')
            ->assertRedirect(route('dashboard'));
    }

    /** @test */
    public function validation_rules_are_applied()
    {
        Livewire::test(LoginForm::class)
            ->set('username', '')
            ->set('password', '')
            ->call('login')
            ->assertHasErrors(['username' => 'required', 'password' => 'required']);
    }

    /** @test */
    public function invalid_credentials_show_error()
    {
        // Mock de la API externa devolviendo error
        Http::fake([
            '*/auth/login' => Http::response(['success' => false, 'mensaje' => 'Usuario o contraseña incorrectos'], 401)
        ]);

        Livewire::test(LoginForm::class)
            ->set('username', 'wronguser')
            ->set('password', 'wrongpassword')
            ->call('login')
            ->assertSet('hasError', true);
    }

    /** @test */
    public function successful_login_redirects_to_dashboard()
    {
        // Mock de la API externa con la estructura real
        Http::fake([
            '*/auth/login' => Http::response([
                'success' => true,
                'accessToken' => 'fake-token',
                'sessionId' => 'my-fake-session-id',
                'user' => [
                    'CodigoAgente' => 140,
                    'Nombre' => 'JORGE EDUARDO PEÑA RAMIREZ',
                    'Correo' => 'jpramirez@grupoimpulsora.com',
                    'u_zona' => 'FM',
                    'U_Cobranza' => 'Comercial',
                    'Code' => 140,
                    'tipoAgente' => '2'
                ]
            ], 200)
        ]);

        Livewire::test(LoginForm::class)
            ->set('username', 'jpramirez')
            ->set('password', 'password123') // 11 caracteres (mínimo 8)
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        
        // Verificar que el sessionId se guardó en el usuario autenticado
        $this->assertEquals('my-fake-session-id', auth()->user()->sessionId);
    }

    /** @test */
    public function user_with_invalid_role_cannot_login()
    {
        // Mock de la API externa con un rol diferente a '2'
        Http::fake([
            '*/auth/login' => Http::response([
                'success' => true,
                'accessToken' => 'fake-token',
                'sessionId' => 'my-fake-session-id',
                'user' => [
                    'CodigoAgente' => 140,
                    'Nombre' => 'USUARIO SIN PERMISOS',
                    'Correo' => 'invalid@grupoimpulsora.com',
                    'tipoAgente' => '3' // Rol inválido
                ]
            ], 200)
        ]);

        Livewire::test(LoginForm::class)
            ->set('username', 'invalid')
            ->set('password', 'password123')
            ->call('login')
            ->assertNoRedirect()
            ->assertHasErrors(['auth_error']);

        $this->assertGuest();
    }

    /** @test */
    public function successful_logout_calls_external_api()
    {
        Http::fake([
            '*/auth/logout' => Http::response(['success' => true], 200)
        ]);

        $user = new \App\Infrastructure\Auth\Models\AuthenticatedUser([
            'id' => 1,
            'name' => 'Test User',
            'rol' => 'Asesor Agrónomo',
            'sessionId' => 'active-session-123'
        ]);

        $this->actingAs($user);

        $this->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();

        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'auth/logout') &&
                   $request['sessionId'] === 'active-session-123';
        });
    }
}
