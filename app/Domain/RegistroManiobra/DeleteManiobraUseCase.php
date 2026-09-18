<?php

namespace App\Domain\RegistroManiobra;

use Exception;

class DeleteManiobraUseCase
{
    public function __construct(
        private readonly RegistroManiobraRepositoryInterface $repository
    ) {}

    public function execute(int $maniobraId, int|string $usuarioId): string
    {
        if ($maniobraId <= 0) {
            throw new Exception("El ID de la maniobra es inválido.");
        }

        return $this->repository->delete($maniobraId, $usuarioId);
    }
}
