<?php

namespace App\Domain\Corte;

use Exception;

class ConfirmarCorteCuadrillaUseCase
{
    public function __construct(
        private readonly CorteRepositoryInterface $repository
    ) {}

    public function execute(int $corteId, int $cuadrillaId, string $zonaUsuario): void
    {
        $corte = $this->repository->findByZona($zonaUsuario);

        if (!$corte || $corte->id !== $corteId) {
            throw new Exception("Corte no encontrado o no pertenece a esta zona.");
        }

        if ($corte->estado === 'confirmado') {
            throw new Exception("El corte general ya ha sido confirmado.");
        }

        $encontrada = false;
        foreach ($corte->cuadrillas as $cuadrilla) {
            if ($cuadrilla->cuadrillaId === $cuadrillaId) {
                $encontrada = true;
                break;
            }
        }

        if (!$encontrada) {
            throw new Exception("La cuadrilla no pertenece a este corte.");
        }

        $this->repository->marcarCuadrillaConfirmada($corteId, $cuadrillaId);
    }
}
