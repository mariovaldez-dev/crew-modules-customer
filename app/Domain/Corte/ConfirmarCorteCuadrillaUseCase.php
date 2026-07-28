<?php

namespace App\Domain\Corte;

class ConfirmarCorteCuadrillaUseCase
{
    public function __construct(private CorteRepositoryInterface $repository)
    {
    }

    public function execute(int $corteId, int $cuadrillaId, string $zona): void
    {
        // En el SP ya validamos si está activo el corte, aquí solo llamamos al Repo
        $this->repository->confirmarCuadrilla($corteId, $cuadrillaId);
    }
}
