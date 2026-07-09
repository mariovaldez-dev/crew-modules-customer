<?php

namespace App\Domain\Cuadrilla;

use Exception;

class CreateCuadrillaUseCase
{
    public function __construct(
        private readonly CuadrillaRepositoryInterface $repository
    ) {}

    public function execute(
        string $nombre,
        string $lider,
        int $miembros,
        int $puntoVentaId,
        string $zona,
        TarifasManiobra $tarifas
    ): CuadrillaDTO {
        $nombre = trim($nombre);
        $lider = trim($lider);

        if (empty($nombre)) throw new Exception("El nombre de la cuadrilla es obligatorio.");
        if (empty($lider)) throw new Exception("El líder es obligatorio.");
        if ($miembros <= 0) throw new Exception("El número de miembros debe ser mayor a 0.");
        if ($puntoVentaId <= 0) throw new Exception("Debe seleccionar un punto de venta válido.");

        if ($this->repository->exists($nombre, $lider, $puntoVentaId)) {
            throw new Exception("Ya existe una cuadrilla con el mismo nombre y líder en este punto de venta.");
        }

        $dto = new CuadrillaDTO(
            id: null,
            nombre: $nombre,
            lider: $lider,
            miembros: $miembros,
            puntoVentaId: $puntoVentaId,
            zona: $zona,
            tarifas: $tarifas
        );

        return $this->repository->create($dto);
    }
}
