<?php

namespace App\Domain\Cuadrilla;

interface CuadrillaRepositoryInterface
{
    /**
     * @return CuadrillaDTO[]
     */
    public function list(array $filtros, string $zonaUsuario): array;

    public function findById(int $id): ?CuadrillaDTO;

    public function create(CuadrillaDTO $cuadrilla): CuadrillaDTO;

    public function update(int $id, CuadrillaDTO $cuadrilla): CuadrillaDTO;

    public function delete(int $id): bool;

    public function hasLiquidacionesEnProceso(int $cuadrillaId): bool;

    public function hasManiobrasEnProceso(int $cuadrillaId): bool;

    /**
     * Verifica si ya existe una cuadrilla con el mismo nombre en ese punto de venta.
     */
    public function exists(string $nombre, string $lider, string $puntoVentaId, ?int $excludeId = null): bool;
}
