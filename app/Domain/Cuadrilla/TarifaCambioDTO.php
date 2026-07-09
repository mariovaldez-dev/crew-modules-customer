<?php

namespace App\Domain\Cuadrilla;

class TarifaCambioDTO
{
    public function __construct(
        public readonly int $cuadrillaId,
        public readonly string $concepto,
        public readonly ?float $precioAnterior,
        public readonly ?float $precioNuevo,
        public readonly string $usuario,
        public readonly \DateTimeImmutable $fecha
    ) {}
}
