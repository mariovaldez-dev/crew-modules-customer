<?php

namespace App\Domain\Maniobra;

class ListManiobrasUseCase
{
    public function __construct(
        private readonly ManiobraRepositoryInterface $repository
    ) {}

    /**
     * @return ManiobraDTO[]
     */
    public function execute(?string $search = null): array
    {
        return $this->repository->list($search);
    }
}
