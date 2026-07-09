<?php

namespace App\Domain\RegistroManiobra;

class ManiobraManualDTO
{
    public function __construct(
        public readonly \DateTimeImmutable $fecha,
        public readonly int $almacenId,
        public readonly int $cuadrillaId,
        public readonly int $tipoManiobraId,
        public readonly float $toneladas,
        public readonly string $usuarioId,
        public readonly ?string $documentoSap = null
    ) {}
}
