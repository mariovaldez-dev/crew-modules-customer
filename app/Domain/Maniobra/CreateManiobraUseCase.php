<?php

namespace App\Domain\Maniobra;

use Exception;

class CreateManiobraUseCase
{
    public function __construct(
        private readonly ManiobraRepositoryInterface $repository
    ) {}

    /**
     * @throws Exception Si la regla de negocio no se cumple
     */
    public function execute(string $nombre, ?string $descripcion): ManiobraDTO
    {
        $nombre = trim($nombre);
        $descripcion = $descripcion ? trim($descripcion) : null;

        if (empty($nombre)) {
            throw new Exception("El nombre de la maniobra es obligatorio.");
        }

        if ($this->repository->existsByNombre($nombre)) {
            throw new Exception("Ya existe una maniobra registrada con este nombre.");
        }

        if ($descripcion !== null && strlen($descripcion) > 100) {
            throw new Exception("La descripción no puede exceder los 100 caracteres.");
        }

        $dto = new ManiobraDTO(
            id: null,
            nombre: $nombre,
            descripcion: $descripcion,
            estatus: 'A'
        );

        return $this->repository->create($dto);
    }
}
