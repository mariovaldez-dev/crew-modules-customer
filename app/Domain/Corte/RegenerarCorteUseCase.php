<?php

namespace App\Domain\Corte;

use Exception;

class RegenerarCorteUseCase
{
    public function __construct(
        private readonly CorteRepositoryInterface $repository
    ) {}

    public function execute(int $corteId, string $zonaUsuario): void
    {
        $corte = $this->repository->findByZona($zonaUsuario);

        if (!$corte || $corte->id !== $corteId) {
            throw new Exception("Corte no encontrado o no pertenece a esta zona.");
        }

        if ($corte->estado === 'confirmado') {
            throw new Exception("No se puede regenerar un corte que ya está confirmado.");
        }

        // Mock: simulamos nuevas cuadrillas con valores ligeramente distintos para que se note la regeneración
        $nuevasCuadrillas = [
            new CorteCuadrillaDTO(
                cuadrillaId: 1, 
                cuadrillaNombre: 'Cuadrilla Alfa (Regenerada)', 
                puntoVentaNombre: 'Almacén Norte', 
                totalToneladas: 55.0, 
                totalMonto: 13000.00
            ),
        ];

        $this->repository->regenerar($corteId, $nuevasCuadrillas);
    }
}
