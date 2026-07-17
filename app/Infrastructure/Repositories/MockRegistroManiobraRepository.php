<?php

namespace App\Infrastructure\Repositories;

use App\Domain\RegistroManiobra\ManiobraManualDTO;
use App\Domain\RegistroManiobra\RegistroManiobraDTO;
use App\Domain\RegistroManiobra\RegistroManiobraRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class MockRegistroManiobraRepository implements RegistroManiobraRepositoryInterface
{
    private function getData(): array
    {
        return Cache::remember('mock_registro_maniobras', 3600, function () {
            return [
                1 => new RegistroManiobraDTO(
                    id: 1, folio: 'MAN-0001', fecha: new \DateTimeImmutable('-1 day'), 
                    almacenId: 101, almacenNombre: 'Almacén Norte', 
                    cuadrillaId: 1, cuadrillaNombre: 'Cuadrilla Alfa', 
                    tipoManiobraId: 1, tipoManiobraNombre: 'Carga 25kg', 
                    toneladas: 15.500, corteId: null, origen: 'APP'
                ),
                2 => new RegistroManiobraDTO(
                    id: 2, folio: 'MAN-0002', fecha: new \DateTimeImmutable('-2 days'), 
                    almacenId: 102, almacenNombre: 'Almacén Sur', 
                    cuadrillaId: 3, cuadrillaNombre: 'Cuadrilla Sur', 
                    tipoManiobraId: 2, tipoManiobraNombre: 'Descarga 50kg', 
                    toneladas: 8.200, corteId: 50, origen: 'MANUAL'
                ),
                3 => new RegistroManiobraDTO(
                    id: 3, folio: 'MAN-0003', fecha: new \DateTimeImmutable(), 
                    almacenId: 101, almacenNombre: 'Almacén Norte', 
                    cuadrillaId: 2, cuadrillaNombre: 'Cuadrilla Beta', 
                    tipoManiobraId: 3, tipoManiobraNombre: 'Traslado Interno', 
                    toneladas: 20.000, corteId: null, origen: 'APP'
                ),
            ];
        });
    }

    private function saveData(array $data): void
    {
        Cache::put('mock_registro_maniobras', $data, 3600);
    }

    public function almacenes(string $zonaUsuario, string $rolUsuario): array
    {
        if ($rolUsuario === 'CO') {
            return $zonaUsuario === 'ZONA-NORTE' 
                ? [101 => 'Almacén Norte (Mock)'] 
                : [102 => 'Almacén Sur (Mock)'];
        }

        return [
            101 => 'Almacén Norte (Mock)',
            102 => 'Almacén Sur (Mock)'
        ];
    }

    public function list(array $filtros, string $zonaUsuario, string $rolUsuario): array
    {
        $data = $this->getData();

        $filtered = array_filter($data, function (RegistroManiobraDTO $item) use ($filtros, $zonaUsuario, $rolUsuario) {
            
            // Regla: Rol CO solo ve almacenes de su zona (en mock, asumimos que 101 es ZONA-NORTE y 102 ZONA-SUR)
            // Si rol es AM, ve todo.
            if ($rolUsuario === 'CO') {
                $almacenesZona = $zonaUsuario === 'ZONA-NORTE' ? [101] : [102];
                if (!in_array($item->almacenId, $almacenesZona)) {
                    return false;
                }
            }

            // Filtro de Almacén
            if (!empty($filtros['almacenId']) && $item->almacenId != $filtros['almacenId']) {
                return false;
            }

            // Filtro Búsqueda
            if (!empty($filtros['search'])) {
                $s = $filtros['search'];
                if (stripos($item->folio, $s) === false && 
                    stripos($item->cuadrillaNombre, $s) === false &&
                    stripos($item->tipoManiobraNombre, $s) === false) {
                    return false;
                }
            }

            // Filtro Fecha
            $fechaItem = $item->fecha->format('Y-m-d');
            if (!empty($filtros['fechaInicio']) && $fechaItem < $filtros['fechaInicio']) {
                return false;
            }
            if (!empty($filtros['fechaFin']) && $fechaItem > $filtros['fechaFin']) {
                return false;
            }

            // Filtro Estado (este es dinámico según si tiene corte o no)
            $estadoCalculado = $item->corteId ? 'Liquidada' : 'En proceso';
            if (!empty($filtros['estado']) && $estadoCalculado !== $filtros['estado']) {
                return false;
            }

            return true;
        });

        // Ordenar por fecha desc, folio desc
        usort($filtered, function($a, $b) {
            if ($a->fecha == $b->fecha) {
                return $b->folio <=> $a->folio;
            }
            return $b->fecha <=> $a->fecha;
        });

        return array_values($filtered);
    }

    public function create(ManiobraManualDTO $maniobra): RegistroManiobraDTO
    {
        $data = $this->getData();
        $newId = count($data) > 0 ? max(array_keys($data)) + 1 : 1;
        $folio = 'MAN-' . str_pad($newId, 4, '0', STR_PAD_LEFT);

        $almacenes = [101 => 'Almacén Norte', 102 => 'Almacén Sur'];
        $cuadrillas = $this->cuadrillasPorAlmacen($maniobra->almacenId);
        $tipos = $this->tiposManiobra();

        $newDto = new RegistroManiobraDTO(
            id: $newId,
            folio: $folio,
            fecha: $maniobra->fecha,
            almacenId: $maniobra->almacenId,
            almacenNombre: $almacenes[$maniobra->almacenId] ?? 'Desconocido',
            cuadrillaId: $maniobra->cuadrillaId,
            cuadrillaNombre: $cuadrillas[$maniobra->cuadrillaId] ?? 'Desconocida',
            tipoManiobraId: $maniobra->tipoManiobraId,
            tipoManiobraNombre: $tipos[$maniobra->tipoManiobraId] ?? 'Desconocida',
            toneladas: $maniobra->toneladas,
            corteId: null,
            origen: 'MANUAL',
            estado: 'En proceso',
            documentoSap: $maniobra->documentoSap
        );

        $data[$newId] = $newDto;
        $this->saveData($data);

        return $newDto;
    }

    public function cuadrillasPorAlmacen(int $almacenId): array
    {
        // Mock
        if ($almacenId == 101) {
            return [1 => 'Cuadrilla Alfa', 2 => 'Cuadrilla Beta'];
        }
        if ($almacenId == 102) {
            return [3 => 'Cuadrilla Sur'];
        }
        return [];
    }

    public function tiposManiobra(): array
    {
        return [
            1 => 'Carga 25kg',
            2 => 'Descarga 50kg',
            3 => 'Traslado Interno',
            4 => 'Apaleo'
        ];
    }
}
