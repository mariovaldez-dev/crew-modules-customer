<?php

namespace App\Domain\Corte;

class GenerarCorteUseCase
{
    public function __construct(private CorteRepositoryInterface $repository)
    {
    }

    public function execute(string $fechaInicio, string $fechaFin, string $zona): void
    {
        if (strtotime($fechaFin) < strtotime($fechaInicio)) {
            throw new \Exception("La fecha final no puede ser menor a la inicial.");
        }

        $this->repository->generar($zona, $fechaInicio, $fechaFin);
    }
}
