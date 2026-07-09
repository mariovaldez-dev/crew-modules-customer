<?php

namespace App\Domain\Maniobra;

class ManiobraDTO
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $nombre,
        public readonly ?string $descripcion,
        public readonly string $estatus = 'A' // 'A' Activa, 'I' Inactiva
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'estatus' => $this->estatus,
        ];
    }
}
