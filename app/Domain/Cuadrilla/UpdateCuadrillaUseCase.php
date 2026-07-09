<?php

namespace App\Domain\Cuadrilla;

use Exception;

class UpdateCuadrillaUseCase
{
    public function __construct(
        private readonly CuadrillaRepositoryInterface $repository,
        private readonly TarifaAuditRepositoryInterface $auditRepository
    ) {}

    public function execute(
        int $id,
        string $nombre,
        string $lider,
        int $miembros,
        int $puntoVentaId,
        string $zona,
        TarifasManiobra $tarifas,
        string $usuarioId
    ): CuadrillaDTO {
        $nombre = trim($nombre);
        $lider = trim($lider);

        if (empty($nombre)) throw new Exception("El nombre de la cuadrilla es obligatorio.");
        if (empty($lider)) throw new Exception("El líder es obligatorio.");
        if ($miembros <= 0) throw new Exception("El número de miembros debe ser mayor a 0.");

        $cuadrillaActual = $this->repository->findById($id);
        if (!$cuadrillaActual) throw new Exception("Cuadrilla no encontrada.");

        // Regla: Bloquear edición de nombre/PV si hay liquidaciones en proceso
        $cambioRelevante = ($cuadrillaActual->nombre !== $nombre || $cuadrillaActual->puntoVentaId !== $puntoVentaId);
        
        if ($cambioRelevante && $this->repository->hasLiquidacionesEnProceso($id)) {
            throw new Exception("No se puede editar el nombre ni el Punto de Venta porque hay liquidaciones en proceso.");
        }

        if ($this->repository->exists($nombre, $lider, $puntoVentaId, $id)) {
            throw new Exception("Ya existe otra cuadrilla con el mismo nombre y líder en este punto de venta.");
        }

        // Auditar cambios de tarifa
        $this->auditarCambiosDeTarifa($cuadrillaActual->tarifas, $tarifas, $id, $usuarioId);

        $dto = new CuadrillaDTO(
            id: $id,
            nombre: $nombre,
            lider: $lider,
            miembros: $miembros,
            puntoVentaId: $puntoVentaId,
            zona: $zona,
            tarifas: $tarifas
        );

        return $this->repository->update($id, $dto);
    }

    private function auditarCambiosDeTarifa(TarifasManiobra $viejas, TarifasManiobra $nuevas, int $cuadrillaId, string $usuarioId): void
    {
        $v = $viejas->toArray();
        $n = $nuevas->toArray();

        foreach ($n as $concepto => $precioNuevo) {
            $precioViejo = $v[$concepto] ?? null;
            if ($precioNuevo !== $precioViejo) {
                $this->auditRepository->record(new TarifaCambioDTO(
                    cuadrillaId: $cuadrillaId,
                    concepto: $concepto,
                    precioAnterior: $precioViejo,
                    precioNuevo: $precioNuevo,
                    usuario: $usuarioId,
                    fecha: new \DateTimeImmutable()
                ));
            }
        }
    }
}
