<?php

namespace App\Domain\Maniobra;

interface ManiobraRepositoryInterface
{
    /**
     * Obtiene la lista de maniobras, opcionalmente filtrada por búsqueda.
     * @return ManiobraDTO[]
     */
    public function list(?string $search = null): array;

    /**
     * Crea una nueva maniobra en la base de datos.
     */
    public function create(ManiobraDTO $maniobra): ManiobraDTO;

    /**
     * Actualiza una maniobra existente.
     */
    public function update(int $id, ManiobraDTO $maniobra): ManiobraDTO;

    /**
     * Verifica si existe una maniobra con el mismo nombre.
     * Si $excludeId está presente, ignora esa maniobra (útil para edición).
     */
    public function existsByNombre(string $nombre, ?int $excludeId = null): bool;
}
