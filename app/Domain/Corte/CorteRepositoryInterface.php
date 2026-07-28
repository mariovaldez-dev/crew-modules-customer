<?php

namespace App\Domain\Corte;

interface CorteRepositoryInterface
{
    public function generar(string $zona, string $fechaInicio, string $fechaFin): array;
    public function consultar(string $zona): ?array;
    public function confirmarCuadrilla(int $corteId, int $cuadrillaId): array;
    public function confirmarGeneral(int $corteId): array;
    public function regenerar(int $corteId): array;
    public function obtenerUltimaFechaFinConfirmada(string $zona): ?string;
    public function listarCortes(string $zona): array;
    public function consultarCortePorId(int $corteId): array;
}
