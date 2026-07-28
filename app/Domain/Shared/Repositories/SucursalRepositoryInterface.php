<?php

namespace App\Domain\Shared\Repositories;

interface SucursalRepositoryInterface
{
    /**
     * Lista los puntos de venta activos de una zona (vía SP proc_pdm_cosultar_combos 1).
     * Devuelve un array asociativo [WhsCode => WhsName] para poblar combos.
     */
    public function listaPuntosDeVentaPorZona(string $zona): array;

    /**
     * Lista los líderes de cuadrillas por zona (vía SP proc_pdm_cosultar_combos 4).
     * Devuelve un array asociativo [nombre => nombre] para poblar combos.
     */
    public function listaLideresPorZona(string $zona): array;
}
