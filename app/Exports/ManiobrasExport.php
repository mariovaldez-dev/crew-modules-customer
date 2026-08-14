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
     * @param RegistroManiobraDTO|array $maniobra
     */
    public function map($maniobra): array
    {
        $isObj = is_object($maniobra);

        $folio = $isObj ? $maniobra->folio : ($maniobra['folio'] ?? '');
        
        $rawFecha = $isObj ? $maniobra->fecha : ($maniobra['fecha'] ?? null);
        if ($rawFecha instanceof \DateTimeInterface) {
            $fecha = $rawFecha->format('d-m-Y');
        } elseif (is_string($rawFecha) && !empty($rawFecha)) {
            $fecha = (new \DateTimeImmutable($rawFecha))->format('d-m-Y');
        } else {
            $fecha = '';
        }

        $tipo = $isObj ? $maniobra->tipoManiobraNombre : ($maniobra['tipoManiobraNombre'] ?? '');
        $almacen = $isObj ? $maniobra->almacenNombre : ($maniobra['almacenNombre'] ?? '');
        $cuadrilla = $isObj ? $maniobra->cuadrillaNombre : ($maniobra['cuadrillaNombre'] ?? '');
        
        $rawTons = $isObj ? $maniobra->toneladas : ($maniobra['toneladas'] ?? 0);
        $toneladas = rtrim(rtrim(number_format((float)$rawTons, 3, '.', ''), '0'), '.');
        
        $docSap = $isObj ? ($maniobra->documentoSap ?? '') : ($maniobra['documentoSap'] ?? '');
        $estado = $isObj ? $maniobra->estado : ($maniobra['estado'] ?? '');
        
        $estatusCiclo = $isObj ? ($maniobra->estatusCiclo ?? 0) : ($maniobra['estatusCiclo'] ?? 0);
        $folioCorte = $isObj ? ($maniobra->folioCorte ?? null) : ($maniobra['folioCorte'] ?? null);
        $corteId = $isObj ? $maniobra->corteId : ($maniobra['corteId'] ?? null);

        $corte = ($estatusCiclo === 2)
            ? ($folioCorte ?: ($corteId ? 'LIQ-' . str_pad((string)$corteId, 4, '0', STR_PAD_LEFT) : ''))
            : '';

        return [
            $folio,
            $fecha,
            $tipo,
            $almacen,
            $cuadrilla,
            $toneladas,
            $docSap,
            $estado,
            $corte,
        ];
    }

    public function headings(): array
    {
        return [
            'Folio',
            'Fecha',
            'Tipo',
            'Almacen',
            'Cuadrilla',
            'Toneladas',
            'Doc SAP',
            'Estado',
            'Corte'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '16A34A']]],
        ];
    }
}
