<?php

namespace App\Domain\Cuadrilla;

class CuadrillaDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nombre,
        public readonly string $lider,
        public readonly int $miembros,
        public readonly int $puntoVentaId, // PV
        public readonly string $zona, // Zona de origen
        public readonly TarifasManiobra $tarifas
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'lider' => $this->lider,
            'miembros' => $this->miembros,
            'puntoVentaId' => $this->puntoVentaId,
            'zona' => $this->zona,
            'tarifas' => $this->tarifas->toArray(),
        ];
    }
}
