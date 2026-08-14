<?php

namespace App\Domain\Corte;

class EliminarBorradorCorteUseCase
{
    public function __construct(private CorteRepositoryInterface $repository)
    {
    }

    public function execute(int $corteId): array
    {
        return $this->repository->eliminarBorrador($corteId);
    }
}
