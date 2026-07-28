<?php

namespace Tests\Mocks;

use App\Domain\Corte\CorteCuadrillaDTO;
use App\Domain\Corte\CorteDTO;
use App\Domain\Corte\CorteRepositoryInterface;
use Illuminate\Support\Facades\Cache;

class MockCorteRepository implements CorteRepositoryInterface
{
    private function getData(string $zona): ?CorteDTO
    {
        return Cache::get('mock_corte_' . $zona);
    }

    private function saveData(CorteDTO $corte): void
    {
        Cache::put('mock_corte_' . $corte->zona, $corte, 3600);
    }

    public function findByZona(string $zona): ?CorteDTO
    {
        return $this->getData($zona);
    }

    public function crear(string $fechaInicio, string $fechaFin, string $zona, array $cuadrillas): CorteDTO
    {
        // Mock de contador global para folio
        $count = Cache::get('mock_corte_count', 0) + 1;
        Cache::put('mock_corte_count', $count, 3600);
        
        $folio = 'LIQ-' . str_pad($count, 3, '0', STR_PAD_LEFT);

        $corte = new CorteDTO(
            id: $count,
            folio: $folio,
            fechaInicio: $fechaInicio,
            fechaFin: $fechaFin,
            zona: $zona,
            estado: 'borrador',
            cuadrillas: $cuadrillas
        );

        $this->saveData($corte);

        return $corte;
    }

    public function marcarConfirmado(int $corteId): void
    {
        $zonas = ['ZONA-NORTE', 'ZONA-SUR'];
        foreach ($zonas as $zona) {
            $corte = $this->getData($zona);
            if ($corte && $corte->id === $corteId) {
                $corte->estado = 'confirmado';
                $this->saveData($corte);
                return;
            }
        }
    }

    public function marcarCuadrillaConfirmada(int $corteId, int $cuadrillaId): void
    {
        $zonas = ['ZONA-NORTE', 'ZONA-SUR'];
        foreach ($zonas as $zona) {
            $corte = $this->getData($zona);
            if ($corte && $corte->id === $corteId) {
                foreach ($corte->cuadrillas as $cuadrilla) {
                    if ($cuadrilla->cuadrillaId === $cuadrillaId) {
                        $cuadrilla->confirmada = true;
                    }
                }
                $this->saveData($corte);
                return;
            }
        }
    }

    public function regenerar(int $corteId, array $nuevasCuadrillas): CorteDTO
    {
        $zonas = ['ZONA-NORTE', 'ZONA-SUR'];
        foreach ($zonas as $zona) {
            $corte = $this->getData($zona);
            if ($corte && $corte->id === $corteId) {
                $corte->cuadrillas = $nuevasCuadrillas;
                $this->saveData($corte);
                return $corte;
            }
        }
        throw new \Exception("Corte no encontrado");
    }
}
