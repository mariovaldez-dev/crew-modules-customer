<?php

namespace App\Domain\Corte;

class CorteDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $folio,
        public readonly string $fechaInicio,
        public readonly string $fechaFin,
        public readonly string $zona,
        public string $estado, // 'borrador' o 'confirmado'
        /** @var CorteCuadrillaDTO[] */
        public array $cuadrillas = []
    ) {}
}
