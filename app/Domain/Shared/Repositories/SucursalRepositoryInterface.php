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

    /**
     * Lista los puntos de venta activos de una zona (U_SerieSucursal).
     * Devuelve un array asociativo [WhsCode => WhsName] para poblar combos.
     */
    public function listaPuntosDeVentaPorZona(string $zona): array;

    /**
     * Lista los líderes de cuadrillas (filtrado opcional por zona).
     * Devuelve un array asociativo [codigo => nombre] para poblar combos.
     */
    public function listaLideresPorZona(string $zona): array;
}
