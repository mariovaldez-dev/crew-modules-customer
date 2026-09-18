<?php

namespace App\Domain\RegistroManiobra;

use Exception;

class UpdateManiobraManualUseCase
{
    public function __construct(
        private readonly RegistroManiobraRepositoryInterface $repository
    ) {}

    public function execute(
        int $id,
        string $fecha,
        string $almacenId,
        int $cuadrillaId,
        int $tipoManiobraId,
        float $toneladas,
        string|int $usuarioId,
        ?string $documentoSap = null
    ): string {
        if ($id <= 0) throw new Exception("El ID de la maniobra es inválido.");
        if (empty($fecha)) throw new Exception("La fecha es obligatoria.");
        if (empty($almacenId)) throw new Exception("El almacén es obligatorio.");
        if ($cuadrillaId <= 0) throw new Exception("La cuadrilla es obligatoria.");
        if ($tipoManiobraId <= 0) throw new Exception("El tipo de maniobra es obligatorio.");

        $fechaObj = \DateTimeImmutable::createFromFormat('Y-m-d', $fecha);
        if (!$fechaObj) throw new Exception("Formato de fecha inválido.");

        // Regla: No fechas futuras
        $hoy = new \DateTimeImmutable();
        if ($fechaObj > $hoy) {
            throw new Exception("No se pueden registrar maniobras en fechas futuras.");
        }

        // Regla: Validar que la cuadrilla pertenezca al almacén elegido
        $cuadrillasPermitidas = $this->repository->cuadrillasPorAlmacen($almacenId);
        if (!isset($cuadrillasPermitidas[$cuadrillaId])) {
            throw new Exception("La cuadrilla seleccionada no pertenece al almacén elegido.");
        }

        $dto = new ManiobraManualDTO(
            fecha: $fechaObj,
            almacenId: $almacenId,
            cuadrillaId: $cuadrillaId,
            tipoManiobraId: $tipoManiobraId,
            toneladas: round($toneladas, 3),
            usuarioId: (string) $usuarioId,
            documentoSap: $documentoSap
        );

        return $this->repository->update($id, $dto);
    }
}
