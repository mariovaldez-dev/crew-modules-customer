<?php

namespace App\Domain\Shared\Repositories;

interface ICommonRepository
{
    /**
     * Obtiene las categorías de cultivo.
     * @return array
     */
    public function getCategoriasCultivo(): array;

    /**
     * Obtiene los tipos de ciclo de cultivo.
     * @return array
     */
    public function getTiposCicloCultivo(): array;

    /**
     * Obtiene el catálogo de zonas.
     * @return array
     */
    public function getZonas(): array;

    /**
     * Obtiene el catálogo de territorios para una zona.
     * @return array
     */
    public function getTerritorios(string $zonaCodigo): array;

    /**
     * Obtiene el catálogo de productos.
     * @return array
     */
    public function getProductos(): array;
}
