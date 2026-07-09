<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Shared\Repositories\SucursalRepositoryInterface;

class MockSucursalRepository implements SucursalRepositoryInterface
{
    public function zonaDe(int $ownsId): ?string
    {
        if ($ownsId === 101) {
            return 'ZONA-NORTE';
        }
        if ($ownsId === 102) {
            return 'ZONA-SUR';
        }
        return 'ZONA-NORTE';
    }

    public function obtenerPuntosDeVentaDeUsuario(int $usuarioId): array
    {
        // Usuarios CO de prueba
        if ($usuarioId === 2 || $usuarioId === 140) {
            return [101];
        }
        if ($usuarioId === 3) {
            return [102];
        }
        // AM ve todos
        return [101, 102];
    }

    public function obtenerStatusUsuario(int $usuarioId): string
    {
        // Simulamos usuario inactivo si es ID 999
        if ($usuarioId === 999) {
            return 'I';
        }
        return 'A';
    }
}
