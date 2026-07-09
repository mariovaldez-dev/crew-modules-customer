<?php

namespace App\Domain\Corte;

use Exception;

class GenerarCorteUseCase
{
    public function __construct(
        private readonly CorteRepositoryInterface $repository
    ) {}

    public function execute(string $fechaInicio, string $fechaFin, string $zonaUsuario): CorteDTO
    {
        if (empty($fechaInicio) || empty($fechaFin)) {
            throw new Exception("Ambas fechas son obligatorias.");
        }

        if ($fechaFin < $fechaInicio) {
            throw new Exception("La fecha de fin no puede ser menor a la fecha de inicio.");
        }

        $corteActual = $this->repository->findByZona($zonaUsuario);

        if ($corteActual && $corteActual->estado === 'borrador') {
            throw new Exception("Ya existe un corte en borrador para esta zona. Debe confirmarlo antes de generar uno nuevo.");
        }

        // Mock de cálculo: en un sistema real, buscaríamos las maniobras no liquidadas
        // y agruparíamos por cuadrilla para calcular totales.
        // Simularemos este cálculo creando algunas cuadrillas dummy.
        $cuadrillas = [
            new CorteCuadrillaDTO(
                cuadrillaId: 1, 
                cuadrillaNombre: 'Cuadrilla Alfa', 
                puntoVentaNombre: 'Almacén Norte', 
                totalToneladas: 50.5, 
                totalMonto: 12500.00
            ),
            new CorteCuadrillaDTO(
                cuadrillaId: 2, 
                cuadrillaNombre: 'Cuadrilla Beta', 
                puntoVentaNombre: 'Almacén Norte', 
                totalToneladas: 30.0, 
                totalMonto: 7500.00
            ),
        ];

        return $this->repository->crear($fechaInicio, $fechaFin, $zonaUsuario, $cuadrillas);
    }
}
