<?php

namespace App\Domain\Corte;

use Exception;
use Barryvdh\DomPDF\Facade\Pdf;

class GenerarPdfCorteUseCase
{
    public function __construct(private ConsultarCorteUseCase $consultarUseCase)
    {
    }

    public function execute(int $corteId): string
    {
        $corte = $this->consultarUseCase->execute($corteId);

        if (!$corte) {
            throw new \Exception("No se encontró información para generar el reporte");
        }

        if ($corte['estado'] !== 'Confirmado') {
            throw new Exception('Solo se pueden exportar cortes que ya están confirmados.');
        }

        $context = session()->get('usuario_contexto');
        $zonaNombre = ($corte['zonaNombre'] ?? null)
            ?? (($context instanceof \App\Domain\Shared\UsuarioContexto && !empty($context->zonaNombre)) ? $context->zonaNombre : null)
            ?? ($corte['clv_zona'] ?? $corte['zona'] ?? 'Zona');

        // Cargar la vista Blade de dompdf con los datos del corte y el nombre real de la zona
        $pdf = Pdf::loadView('pdf.corte-liquidacion', [
            'corte' => $corte,
            'zona' => $zonaNombre,
        ]);

        // Ajustar el papel (Carta, vertical)
        $pdf->setPaper('letter', 'portrait');

        // Retornar el binario crudo del PDF
        return $pdf->output();
    }
}
