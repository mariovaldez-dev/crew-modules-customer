<?php

namespace App\Domain\Shared;

final class UsuarioContexto
{
    /**
     * @param string $zona `'ZONA-NORTE'`, `'ZONA-SUR'` o `'TODAS'`
     * @param string $tipo `'AM'` o `'CO'`
     * @param int[] $puntosDeVenta Listado de IDs de puntos de venta permitidos
     * @param string $status `'A'` (Activo) o `'I'` (Inactivo)
     */
    public function __construct(
        public readonly string $zona,
        public readonly string $zonaNombre = '',
        public readonly string $tipo = 'CO',
        public readonly array $puntosDeVenta = [],
        public readonly string $status = 'A'
    ) {}

    public function isAdministrador(): bool
    {
        return $this->tipo === 'AM';
    }

    public function isCoordinador(): bool
    {
        return $this->tipo === 'CO';
    }

    public function tieneAccesoAPuntoVenta(int $pvId): bool
    {
        if ($this->isAdministrador()) {
            return true;
        }
        return in_array($pvId, $this->puntosDeVenta, true);
    }
}
