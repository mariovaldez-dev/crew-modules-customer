<?php

namespace App\Domain\Corte;

use Exception;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarPdfCorteUseCase
{
    public function __construct(
        private readonly CorteRepositoryInterface $repository,
        private readonly ObtenerResumenManiobrasUseCase $obtenerResumenUseCase
    ) {}

    public function execute(int $corteId, string $zonaUsuario)
    {
        $corte = $this->repository->findByZona($zonaUsuario);

        if (!$corte || $corte->id !== $corteId) {
            throw new Exception("Corte no encontrado o no pertenece a esta zona.");
        }

        if ($corte->estado !== 'confirmado') {
            throw new Exception("Solo se pueden imprimir cortes confirmados.");
        }

        // Agrupar por PV
        $puntosDeVenta = [];
        
        foreach ($corte->cuadrillas as $cuadrilla) {
            // Requerimiento: Solo se imprimen cuadrillas confirmadas (aunque si el corte está confirmado, todas deben estarlo).
            if (!$cuadrilla->confirmada) continue;

            $pvNombre = $cuadrilla->puntoVentaNombre;
            if (!isset($puntosDeVenta[$pvNombre])) {
                $puntosDeVenta[$pvNombre] = [
                    'nombre' => $pvNombre,
                    'cuadrillas' => []
                ];
            }

            // Obtener el detalle (resumen) real
            $detalle = $this->obtenerResumenUseCase->execute($corteId, $cuadrilla->cuadrillaId);

            $puntosDeVenta[$pvNombre]['cuadrillas'][] = [
                'cuadrilla' => $cuadrilla,
                'detalle' => $detalle
            ];
        }

        $pdf = Pdf::loadView('pdf.corte', [
            'corte' => $corte,
            'puntosDeVenta' => $puntosDeVenta,
            'zona' => $zonaUsuario
        ]);

        return $pdf->output();
    }
}
