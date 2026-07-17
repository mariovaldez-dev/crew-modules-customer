<?php

namespace App\Exports;

use App\Domain\RegistroManiobra\RegistroManiobraDTO;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ManiobrasExport implements FromArray, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    public function __construct(
        private readonly array $maniobras
    ) {}

    public function array(): array
    {
        return $this->maniobras;
    }

    /**
     * @param RegistroManiobraDTO $maniobra
     */
    public function map($maniobra): array
    {
        return [
            $maniobra->folio,
            $maniobra->fecha->format('Y-m-d'),
            $maniobra->almacenNombre,
            $maniobra->cuadrillaNombre,
            $maniobra->tipoManiobraNombre,
            number_format($maniobra->toneladas, 3, '.', ''),
            $maniobra->estado,
            $maniobra->origen,
            $maniobra->corteId ?? 'N/A'
        ];
    }

    public function headings(): array
    {
        return [
            'Folio',
            'Fecha',
            'Almacén',
            'Cuadrilla',
            'Tipo de Maniobra',
            'Toneladas',
            'Estatus',
            'Origen',
            'Corte de Liq.'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '16A34A']]],
        ];
    }
}
