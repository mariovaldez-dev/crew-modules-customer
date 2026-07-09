<?php

namespace App\Domain\Shared\Repositories;

interface SucursalRepositoryInterface
{
    /**
     * Obtiene la zona asociada a un almacén/punto de venta (OWHS).
     */
    public function zonaDe(int $ownsId): ?string;

    /**
     * Obtiene los puntos de venta asignados a un usuario.
     * @return int[]
     */
    public function obtenerPuntosDeVentaDeUsuario(int $usuarioId): array;

    /**
     * Obtiene el estatus del usuario desde SAP B1.
     */
    public function obtenerStatusUsuario(int $usuarioId): string;
}
