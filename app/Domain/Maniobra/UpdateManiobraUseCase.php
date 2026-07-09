<?php

namespace App\Domain\Maniobra;

use Exception;

class UpdateManiobraUseCase
{
    public function __construct(
        private readonly ManiobraRepositoryInterface $repository
    ) {}

    /**
     * @throws Exception Si la regla de negocio no se cumple
     */
    public function execute(int $id, string $nombre, ?string $descripcion, string $estatus): ManiobraDTO
    {
        $nombre = trim($nombre);
        $descripcion = $descripcion ? trim($descripcion) : null;

        if (empty($nombre)) {
            throw new Exception("El nombre de la maniobra es obligatorio.");
        }

        if ($this->repository->existsByNombre($nombre, $id)) {
            throw new Exception("Ya existe otra maniobra registrada con este nombre.");
        }

        if ($descripcion !== null && strlen($descripcion) > 100) {
            throw new Exception("La descripción no puede exceder los 100 caracteres.");
        }

        if (!in_array($estatus, ['A', 'I'])) {
            throw new Exception("El estatus proporcionado no es válido.");
        }

        $dto = new ManiobraDTO(
            id: $id,
            nombre: $nombre,
            descripcion: $descripcion,
            estatus: $estatus
        );

        return $this->repository->update($id, $dto);
    }
}
