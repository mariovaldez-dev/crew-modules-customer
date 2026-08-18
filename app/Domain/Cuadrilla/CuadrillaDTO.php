<?php

namespace App\Domain\Cuadrilla;

class CuadrillaDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nombre,
        public readonly string $lider,
        public readonly int $miembros,
        public readonly string $puntoVentaId, // WhsCode de OWHS (ej. 'ANGOS02')
        public readonly string $zona,
        public readonly TarifasManiobra $tarifas,
        public readonly ?string $puntoVentaNombre = null
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'lider' => $this->lider,
            'miembros' => $this->miembros,
            'puntoVentaId' => $this->puntoVentaId,
            'puntoVentaNombre' => $this->puntoVentaNombre ?: $this->puntoVentaId,
            'zona' => $this->zona,
            'tarifas' => $this->tarifas->toArray(),
        ];
    }
}
