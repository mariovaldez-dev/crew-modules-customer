<?php

namespace App\Domain\Corte;

class ObtenerResumenManiobrasUseCase
{
    public function execute(int $corteId, int $cuadrillaId): array
    {
        // Mock: Resumen de maniobras para una cuadrilla dentro del corte
        return [
            [
                'folio' => 'MAN-0010',
                'concepto' => 'Carga 25kg',
                'toneladas' => 20.5,
                'precio' => 150.00,
                'total' => 3075.00
            ],
            [
                'folio' => 'MAN-0012',
                'concepto' => 'Descarga 50kg',
                'toneladas' => 30.0,
                'precio' => 180.00,
                'total' => 5400.00
            ]
        ];
    }
}
