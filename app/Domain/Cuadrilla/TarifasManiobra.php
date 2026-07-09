<?php

namespace App\Domain\Cuadrilla;

class TarifasManiobra
{
    public function __construct(
        public readonly ?float $carga25 = null,
        public readonly ?float $carga50 = null,
        public readonly ?float $descarga25 = null,
        public readonly ?float $descarga50 = null,
        public readonly ?float $traslado = null,
        public readonly ?float $apaleo = null
    ) {
        $this->validateNoNegativos();
    }

    private function validateNoNegativos(): void
    {
        $tarifas = [
            'Carga 25kg' => $this->carga25,
            'Carga 50kg' => $this->carga50,
            'Descarga 25kg' => $this->descarga25,
            'Descarga 50kg' => $this->descarga50,
            'Traslado' => $this->traslado,
            'Apaleo' => $this->apaleo,
        ];

        foreach ($tarifas as $nombre => $valor) {
            if ($valor !== null && $valor < 0) {
                throw new \InvalidArgumentException("La tarifa para $nombre no puede ser negativa.");
            }
        }
    }

    public function toArray(): array
    {
        return [
            'carga25' => $this->carga25,
            'carga50' => $this->carga50,
            'descarga25' => $this->descarga25,
            'descarga50' => $this->descarga50,
            'traslado' => $this->traslado,
            'apaleo' => $this->apaleo,
        ];
    }
}
