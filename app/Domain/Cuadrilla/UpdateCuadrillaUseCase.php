<?php

namespace App\Domain\Cuadrilla;

use Exception;

class UpdateCuadrillaUseCase
{
    public function __construct(
        private readonly CuadrillaRepositoryInterface $repository
    ) {}

    /**
     * @throws Exception
     */
    public function execute(
        int $id,
        string $nombre,
        string $lider,
        int $miembros,
        string $puntoVentaId,   // WhsCode (ej. 'ANGOS02')
        string $zona,
        TarifasManiobra $tarifas,
        string $usuarioId       // mantenemos para compatibilidad con el componente
    ): CuadrillaDTO {
        $nombre = trim($nombre);
        $lider  = trim($lider);

        if (empty($nombre)) throw new Exception('El nombre de la cuadrilla es obligatorio.');
        if (empty($lider))  throw new Exception('El líder es obligatorio.');
        if ($miembros <= 0) throw new Exception('El número de miembros debe ser mayor a 0.');

        $cuadrillaActual = $this->repository->findById($id);
        if (!$cuadrillaActual) throw new Exception('Cuadrilla no encontrada.');

        // Regla: bloquear edición de nombre/PV si hay liquidaciones en proceso
        $cambioRelevante = ($cuadrillaActual->nombre !== $nombre ||
                            $cuadrillaActual->puntoVentaId !== $puntoVentaId);

        if ($cambioRelevante && $this->repository->hasLiquidacionesEnProceso($id)) {
            throw new Exception(
                'No se puede editar el nombre ni el Punto de Venta porque hay liquidaciones en proceso.'
            );
        }

        if ($this->repository->exists($nombre, $lider, $puntoVentaId, $id)) {
            throw new Exception('Ya existe otra cuadrilla con el mismo nombre en este punto de venta.');
        }

        // Nota: la auditoría de cambios de tarifa la maneja el SP
        // (his_pdm_tarifas_cuadrillas) — no se necesita la capa PHP de auditoría.

        $dto = new CuadrillaDTO(
            id:          $id,
            nombre:      $nombre,
            lider:       $lider,
            miembros:    $miembros,
            puntoVentaId: $puntoVentaId,
            zona:        $zona,
            tarifas:     $tarifas
        );

        return $this->repository->update($id, $dto);
    }
}
