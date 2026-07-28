<?php

namespace Tests\Feature\Auth;

use App\Domain\Auth\Entities\User;
use App\Domain\Auth\Repositories\IAuthRepository;
use App\UI\Livewire\Auth\LoginForm;
use Livewire\Livewire;
use Tests\TestCase;
use Mockery;

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
        $mockRepo = Mockery::mock(IAuthRepository::class);
        $mockRepo->shouldReceive('authenticate')->andReturn(null);
        $mockRepo->shouldReceive('logAttempt');
        $this->app->instance(IAuthRepository::class, $mockRepo);

        Livewire::test(LoginForm::class)
            ->set('username', 'wronguser')
            ->set('password', 'wrongpassword')
            ->call('login')
            ->assertSet('hasError', true);
    }

    /** @test */
    public function successful_login_redirects_to_dashboard()
    {
        $mockRepo = Mockery::mock(IAuthRepository::class);
        $mockRepo->shouldReceive('authenticate')
            ->andReturn(new User(
                id: 140,
                name: 'JORGE EDUARDO PEÑA RAMIREZ',
                email: 'jpramirez@grupoimpulsora.com',
                rol: 'CO',
                activo: true,
                sessionId: 'my-fake-session-id',
                zona: 'FM'
            ));
        $mockRepo->shouldReceive('logAttempt');
        $this->app->instance(IAuthRepository::class, $mockRepo);

        Livewire::test(LoginForm::class)
            ->set('username', 'jpramirez')
            ->set('password', 'password123')
            ->call('login')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        
        $this->assertEquals('my-fake-session-id', auth()->user()->sessionId);
    }

    /** @test */
    public function user_with_invalid_role_cannot_login()
    {
        $mockRepo = Mockery::mock(IAuthRepository::class);
        $mockRepo->shouldReceive('authenticate')
            ->andReturn(new User(
                id: 140,
                name: 'USUARIO SIN PERMISOS',
                email: 'invalid@grupoimpulsora.com',
                rol: 'INVALID_ROLE',
                activo: true,
                sessionId: 'my-fake-session-id',
                zona: 'FM'
            ));
        $mockRepo->shouldReceive('logAttempt');
        $this->app->instance(IAuthRepository::class, $mockRepo);

        Livewire::test(LoginForm::class)
            ->set('username', 'invalid')
            ->set('password', 'password123')
            ->call('login')
            ->assertNoRedirect()
            ->assertHasErrors(['auth_error']);

        $this->assertGuest();
    }

    /** @test */
    public function successful_logout_clears_session()
    {
        $user = new \App\Infrastructure\Auth\Models\AuthenticatedUser([
            'id' => 1,
            'name' => 'Test User',
            'rol' => 'CO',
            'sessionId' => 'active-session-123'
        ]);

        $this->actingAs($user);

        $this->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
