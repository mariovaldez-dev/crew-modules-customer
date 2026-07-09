<?php

namespace App\Domain\Cuadrilla;

use Exception;

class DeleteCuadrillaUseCase
{
    public function __construct(
        private readonly CuadrillaRepositoryInterface $repository
    ) {}

    public function execute(int $id): bool
    {
        if ($this->repository->hasManiobrasEnProceso($id)) {
            throw new Exception("No se puede eliminar la cuadrilla porque tiene maniobras en proceso.");
        }

        return $this->repository->delete($id);
    }
}
