<?php

namespace App\Domain\Corte;

class ObtenerUltimaFechaFinCorteUseCase
{
    public function __construct(private CorteRepositoryInterface $repository)
    {
    }

    public function execute(string $zona): ?string
    {
        return $this->repository->obtenerUltimaFechaFinConfirmada($zona);
    }
}
