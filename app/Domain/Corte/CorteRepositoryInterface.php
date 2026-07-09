<?php

namespace App\Domain\Corte;

interface CorteRepositoryInterface
{
    public function findByZona(string $zona): ?CorteDTO;
    
    public function crear(string $fechaInicio, string $fechaFin, string $zona, array $cuadrillas): CorteDTO;
    
    public function marcarConfirmado(int $corteId): void;

    public function marcarCuadrillaConfirmada(int $corteId, int $cuadrillaId): void;

    public function regenerar(int $corteId, array $nuevasCuadrillas): CorteDTO;
}
