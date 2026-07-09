<?php

namespace App\Infrastructure\Repositories;

use App\Domain\Cuadrilla\CuadrillaDTO;
use App\Domain\Cuadrilla\CuadrillaRepositoryInterface;
use App\Domain\Cuadrilla\TarifasManiobra;
use Illuminate\Support\Facades\Cache;

class MockCuadrillaRepository implements CuadrillaRepositoryInterface
{
    private function getData(): array
    {
        return Cache::remember('mock_cuadrillas', 3600, function () {
            return [
                1 => new CuadrillaDTO(1, 'Cuadrilla Alfa', 'Juan Pérez', 4, 101, 'ZONA-NORTE', new TarifasManiobra(10.5, 20.0, 5.0, 10.0, null, 15.0)),
                2 => new CuadrillaDTO(2, 'Cuadrilla Beta', 'Carlos Gómez', 5, 101, 'ZONA-NORTE', new TarifasManiobra(12.0, 22.0, 6.0, 11.0, null, null)),
                3 => new CuadrillaDTO(3, 'Cuadrilla Sur', 'Pedro López', 3, 102, 'ZONA-SUR', new TarifasManiobra(9.5, 19.0, 4.0, 9.0, 50.0, 10.0)),
            ];
        });
    }

    private function saveData(array $data): void
    {
        Cache::put('mock_cuadrillas', $data, 3600);
    }

    public function list(array $filtros, string $zonaUsuario): array
    {
        $data = $this->getData();

        // Aplicar filtros
        $filtered = array_filter($data, function (CuadrillaDTO $item) use ($filtros, $zonaUsuario) {
            // Filtro de Zona Obligatorio
            if ($zonaUsuario !== 'TODAS' && $item->zona !== $zonaUsuario) {
                return false;
            }

            if (!empty($filtros['search']) && stripos($item->nombre, $filtros['search']) === false) {
                return false;
            }

            if (!empty($filtros['lider']) && stripos($item->lider, $filtros['lider']) === false) {
                return false;
            }

            if (!empty($filtros['puntoVentaId']) && $item->puntoVentaId != $filtros['puntoVentaId']) {
                return false;
            }

            return true;
        });

        return array_values($filtered);
    }

    public function findById(int $id): ?CuadrillaDTO
    {
        $data = $this->getData();
        return $data[$id] ?? null;
    }

    public function create(CuadrillaDTO $cuadrilla): CuadrillaDTO
    {
        $data = $this->getData();
        $newId = count($data) > 0 ? max(array_keys($data)) + 1 : 1;
        
        $newDto = new CuadrillaDTO(
            id: $newId,
            nombre: $cuadrilla->nombre,
            lider: $cuadrilla->lider,
            miembros: $cuadrilla->miembros,
            puntoVentaId: $cuadrilla->puntoVentaId,
            zona: $cuadrilla->zona,
            tarifas: $cuadrilla->tarifas
        );

        $data[$newId] = $newDto;
        $this->saveData($data);

        return $newDto;
    }

    public function update(int $id, CuadrillaDTO $cuadrilla): CuadrillaDTO
    {
        $data = $this->getData();
        $data[$id] = new CuadrillaDTO(
            id: $id,
            nombre: $cuadrilla->nombre,
            lider: $cuadrilla->lider,
            miembros: $cuadrilla->miembros,
            puntoVentaId: $cuadrilla->puntoVentaId,
            zona: $cuadrilla->zona,
            tarifas: $cuadrilla->tarifas
        );
        $this->saveData($data);

        return $data[$id];
    }

    public function delete(int $id): bool
    {
        $data = $this->getData();
        if (isset($data[$id])) {
            unset($data[$id]);
            $this->saveData($data);
            return true;
        }
        return false;
    }

    public function hasLiquidacionesEnProceso(int $cuadrillaId): bool
    {
        // Mock logic: let's pretend Cuadrilla ID 1 has liquidations in process
        return $cuadrillaId === 1;
    }

    public function hasManiobrasEnProceso(int $cuadrillaId): bool
    {
        // Mock logic: pretend Cuadrilla ID 2 has maneuvers in process
        return $cuadrillaId === 2;
    }

    public function exists(string $nombre, string $lider, int $puntoVentaId, ?int $excludeId = null): bool
    {
        foreach ($this->getData() as $id => $item) {
            if ($excludeId !== null && $id === $excludeId) {
                continue;
            }
            if (strtolower($item->nombre) === strtolower($nombre) &&
                strtolower($item->lider) === strtolower($lider) &&
                $item->puntoVentaId === $puntoVentaId) {
                return true;
            }
        }
        return false;
    }
}
