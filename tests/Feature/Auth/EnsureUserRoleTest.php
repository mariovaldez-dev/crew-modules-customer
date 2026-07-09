<?php

namespace Tests\Feature\Auth;

use App\Domain\Shared\UsuarioContexto;
use App\Infrastructure\Auth\Models\AuthenticatedUser;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class EnsureUserRoleTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->singleton(
            \App\Domain\Shared\Repositories\SucursalRepositoryInterface::class,
            \App\Infrastructure\Repositories\MockSucursalRepository::class
        );
    }
    /** @test */
    public function active_am_role_can_access_dashboard()
    {
        $user = new AuthenticatedUser([
            'id' => 1,
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'rol' => 'AM',
            'sessionId' => 'session-123'
        ]);

        $context = new UsuarioContexto(
            zona: 'TODAS',
            tipo: 'AM',
            puntosDeVenta: [],
            status: 'A'
        );

        session(['authenticated_user' => $user]);
        session(['usuario_contexto' => $context]);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function active_co_role_can_access_dashboard()
    {
        $user = new AuthenticatedUser([
            'id' => 2,
            'name' => 'Coordinadora User',
            'email' => 'coord@example.com',
            'rol' => 'CO',
            'sessionId' => 'session-456'
        ]);

        $context = new UsuarioContexto(
            zona: 'ZONA-NORTE',
            tipo: 'CO',
            puntosDeVenta: [101],
            status: 'A'
        );

        session(['authenticated_user' => $user]);
        session(['usuario_contexto' => $context]);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200);
    }

    /** @test */
    public function inactive_user_is_forbidden()
    {
        $user = new AuthenticatedUser([
            'id' => 3,
            'name' => 'Inactive User',
            'email' => 'inactive@example.com',
            'rol' => 'CO',
            'sessionId' => 'session-789'
        ]);

        $context = new UsuarioContexto(
            zona: 'ZONA-NORTE',
            tipo: 'CO',
            puntosDeVenta: [101],
            status: 'I' // Inactivo
        );

        session(['authenticated_user' => $user]);
        session(['usuario_contexto' => $context]);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function invalid_role_is_forbidden()
    {
        $user = new AuthenticatedUser([
            'id' => 4,
            'name' => 'Guest User',
            'email' => 'guest@example.com',
            'rol' => '3', // Invalid role
            'sessionId' => 'session-000'
        ]);

        $context = new UsuarioContexto(
            zona: 'ZONA-NORTE',
            tipo: '3',
            puntosDeVenta: [],
            status: 'A'
        );

        session(['authenticated_user' => $user]);
        session(['usuario_contexto' => $context]);

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(403);
    }

    /** @test */
    public function missing_context_is_resolved_on_the_fly()
    {
        $user = new AuthenticatedUser([
            'id' => 2, // ID 2 is mocked to have PV 101 / ZONA-NORTE
            'name' => 'Coordinadora User',
            'email' => 'coord@example.com',
            'rol' => 'CO',
            'sessionId' => 'session-456'
        ]);

        session(['authenticated_user' => $user]);
        // Do not set session('usuario_contexto') explicitly to test on-the-fly resolution

        $response = $this->actingAs($user)
            ->get('/dashboard');

        $response->assertStatus(200);

        // Verify that the context was indeed generated and stored in session
        $context = session('usuario_contexto');
        $this->assertInstanceOf(UsuarioContexto::class, $context);
        $this->assertEquals('CO', $context->tipo);
        $this->assertEquals('ZONA-NORTE', $context->zona);
        $this->assertEquals('A', $context->status);
    }
}
