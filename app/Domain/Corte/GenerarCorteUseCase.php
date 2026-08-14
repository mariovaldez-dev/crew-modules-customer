<?php

namespace App\Domain\Corte;

class GenerarCorteUseCase
{
    public function __construct(private CorteRepositoryInterface $repository)
    {
    }

    public function execute(string $fechaInicio, string $fechaFin, string $zona, bool $reemplazarBorrador = false): array
    {
        if (strtotime($fechaFin) < strtotime($fechaInicio)) {
            throw new \Exception("La fecha final no puede ser menor a la inicial.");
        }

        return $this->repository->generar($zona, $fechaInicio, $fechaFin, $reemplazarBorrador);
    }
}
