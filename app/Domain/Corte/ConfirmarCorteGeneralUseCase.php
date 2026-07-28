<?php

namespace App\Domain\Corte;

class ConfirmarCorteGeneralUseCase
{
    public function __construct(private CorteRepositoryInterface $repository)
    {
    }

    public function execute(int $corteId, string $zona): array
    {
        return $this->repository->confirmarGeneral($corteId);
    }
}
