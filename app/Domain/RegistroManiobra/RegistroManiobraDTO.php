<?php

namespace App\Domain\RegistroManiobra;

class RegistroManiobraDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $folio,
        public readonly \DateTimeImmutable $fecha,
        public readonly string $almacenId,
        public readonly string $almacenNombre,
        public readonly int $cuadrillaId,
        public readonly string $cuadrillaNombre,
        public readonly int $tipoManiobraId,
        public readonly string $tipoManiobraNombre,
        public readonly float $toneladas,
        public readonly ?int $corteId,
        public readonly ?string $folioCorte = null,
        // 0 = En proceso | 1 = Confirmada | 2 = Liquidada — calculado por el SP
        public readonly int $estatusCiclo = 0,
        public readonly string $origen = 'APP', // 'APP' o 'MANUAL'
        public string $estado = 'En proceso',   // Mapeado a string por el UseCase
        public readonly ?string $documentoSap = null
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'folio' => $this->folio,
            'fecha' => $this->fecha->format('Y-m-d'),
            'almacenId' => $this->almacenId,
            'almacenNombre' => $this->almacenNombre,
            'cuadrillaId' => $this->cuadrillaId,
            'cuadrillaNombre' => $this->cuadrillaNombre,
            'tipoManiobraId' => $this->tipoManiobraId,
            'tipoManiobraNombre' => $this->tipoManiobraNombre,
            'toneladas' => $this->toneladas,
            'corteId' => $this->corteId,
            'folioCorte' => $this->folioCorte,
            'estatusCiclo' => $this->estatusCiclo,
            'origen' => $this->origen,
            'estado' => $this->estado,
            'documentoSap' => $this->documentoSap,
        ];
    }
}
