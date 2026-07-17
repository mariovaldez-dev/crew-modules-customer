<?php

namespace App\Domain\RegistroManiobra;

interface RegistroManiobraRepositoryInterface
{
    /**
     * @return RegistroManiobraDTO[]
     */
    public function list(array $filtros, string $zonaUsuario, string $rolUsuario): array;

    public function create(ManiobraManualDTO $maniobra): string;

    /**
     * @return array<int, string>
     */
    public function cuadrillasPorAlmacen(string $almacenId): array;

    /**
     * @return array<int, string>
     */
    public function tiposManiobra(): array;
}
