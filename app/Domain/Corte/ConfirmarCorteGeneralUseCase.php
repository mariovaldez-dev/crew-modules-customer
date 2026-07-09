<?php

namespace App\Domain\Corte;

use Exception;

class ConfirmarCorteGeneralUseCase
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
            throw new Exception("El corte ya está confirmado.");
        }

        foreach ($corte->cuadrillas as $cuadrilla) {
            if (!$cuadrilla->confirmada) {
                throw new Exception("No se puede confirmar el corte general. La cuadrilla {$cuadrilla->cuadrillaNombre} no ha sido confirmada.");
            }
        }

        $this->repository->marcarConfirmado($corteId);
    }
}
