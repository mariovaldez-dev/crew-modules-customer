<?php

namespace App\Domain\Cuadrilla;

class ListCuadrillasUseCase
{
    public function __construct(
        private readonly CuadrillaRepositoryInterface $repository
    ) {}

    /**
     * @return CuadrillaDTO[]
     */
    public function execute(array $filtros, string $zonaUsuario): array
    {
        return $this->repository->list($filtros, $zonaUsuario);
    }
}
