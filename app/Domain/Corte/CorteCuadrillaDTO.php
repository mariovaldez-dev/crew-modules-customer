<?php

namespace App\Domain\Corte;

class CorteCuadrillaDTO
{
    public function __construct(
        public readonly int $cuadrillaId,
        public readonly string $cuadrillaNombre,
        public readonly string $puntoVentaNombre,
        public readonly float $totalToneladas,
        public readonly float $totalMonto,
        public bool $confirmada = false
    ) {}
}
