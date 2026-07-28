<?php

namespace App\Domain\Corte;

class ListarCortesUseCase
{
    public function __construct(private CorteRepositoryInterface $repository)
    {
    }

    public function execute(string $zona): array
    {
        return $this->repository->listarCortes($zona);
    }
}
