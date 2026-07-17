<?php

namespace App\Domain\RegistroManiobra;

use Exception;

class ExportManiobrasUseCase
{
    public function __construct(
        private readonly RegistroManiobraRepositoryInterface $repository
    ) {}

    public function executeConDatos(array $maniobras, array $filtros)
    {
        if (count($maniobras) === 0) {
            throw new Exception("No existe información para exportar.");
        }

        $export = new \App\Exports\ManiobrasExport($maniobras);
        $inicio = $filtros['fechaInicio'] ?? date('Y-m-d');
        $fin = $filtros['fechaFin'] ?? date('Y-m-d');
        $fileName = 'registro_maniobras_' . $inicio . '_al_' . $fin . '.xlsx';
        
        return response()->streamDownload(function () use ($export) {
            echo \Maatwebsite\Excel\Facades\Excel::raw($export, \Maatwebsite\Excel\Excel::XLSX);
        }, $fileName);
    }
}
