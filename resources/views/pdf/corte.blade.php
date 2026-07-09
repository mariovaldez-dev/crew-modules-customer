<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corte de Liquidación {{ $corte->folio }}</title>
    <style>
        @page {
            margin: 40px 50px;
        }
        body {
            font-family: Arial, sans-serif;
            font-size: 13px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .text-green { color: #009e4f; }
        .text-lg { font-size: 22px; }
        .text-md { font-size: 16px; }
        .text-sm { font-size: 11px; }

        /* Top Header */
        .top-header {
            width: 100%;
            margin-bottom: 20px;
        }
        .top-header td {
            vertical-align: top;
        }
        .logo {
            color: #009e4f;
            font-size: 24px;
            font-weight: bold;
        }
        .logo-icon {
            display: inline-block;
            width: 16px;
            height: 16px;
            background-color: #ffc107;
            border-radius: 50%;
            margin-right: 5px;
        }
        .folio-box {
            font-weight: bold;
            font-size: 14px;
        }

        /* Title Area */
        .title-area {
            text-align: center;
            margin-bottom: 20px;
        }
        .title-area h1 {
            font-size: 24px;
            margin: 0 0 5px 0;
            font-weight: bold;
        }

        /* Info Card */
        .info-card {
            width: 100%;
            border: 1px solid #d1d5db;
            border-radius: 5px;
            margin-bottom: 30px;
            border-collapse: collapse;
        }
        .info-card td {
            padding: 12px;
            text-align: center;
            vertical-align: middle;
            border-right: 1px solid #d1d5db;
        }
        .info-card td:last-child {
            border-right: none;
        }
        .info-label {
            font-weight: bold;
            font-size: 12px;
            color: #000;
        }
        .info-value {
            font-weight: bold;
            font-size: 14px;
            color: #009e4f;
            margin-top: 3px;
        }

        /* Cuadrilla Table */
        .cuadrilla-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .cuadrilla-table th, .cuadrilla-table td {
            border: 1px solid #d1d5db;
            padding: 8px;
            text-align: center;
        }
        .cuadrilla-header {
            background-color: #009e4f;
            color: #000;
            font-weight: bold;
            font-size: 15px;
            padding: 10px;
            border: 1px solid #009e4f;
        }
        .col-headers th {
            background-color: #eaf5ec;
            font-weight: bold;
        }
        .total-row td {
            font-weight: bold;
        }

        /* Total General Box */
        .total-general-box {
            width: 100%;
            border: 1px solid #75d481;
            background-color: #f0fdf4;
            margin-top: 40px;
            border-collapse: collapse;
        }
        .total-general-box td {
            padding: 20px;
            text-align: center;
            vertical-align: middle;
            font-size: 16px;
            font-weight: bold;
        }
        .total-general-label {
            color: #000;
            font-size: 18px;
            text-align: left;
            padding-left: 30px !important;
            width: 33%;
        }
        .total-general-tons {
            color: #009e4f;
            width: 33%;
        }
        .total-general-money {
            color: #009e4f;
            width: 33%;
        }

        /* Signature */
        .signature-area {
            width: 100%;
            margin-top: 80px;
            page-break-inside: avoid;
        }
        .signature-line {
            width: 250px;
            border-bottom: 1px solid #000;
            margin: 0 auto;
        }
        .signature-text {
            text-align: center;
            font-weight: bold;
            margin-top: 8px;
            font-size: 15px;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>
<body>

    @foreach($puntosDeVenta as $pv)
        
        <!-- Header -->
        <table class="top-header">
            <tr>
                <td class="text-left">
                    <div class="logo">
                        <span class="logo-icon"></span> Impulsora
                    </div>
                </td>
                <td class="text-right folio-box">
                    {{ $corte->folio }}
                </td>
            </tr>
        </table>

        <!-- Title -->
        <div class="title-area">
            <h1>Corte de maniobras</h1>
            <p class="font-bold text-md">
                {{ \Carbon\Carbon::parse($corte->fechaInicio)->isoFormat('D \d\e MMMM') }} al {{ \Carbon\Carbon::parse($corte->fechaFin)->isoFormat('D \d\e MMMM') }}
            </p>
        </div>

        <!-- Info Card -->
        <table class="info-card">
            <tr>
                <td style="width: 33%;">
                    <div class="info-label">Zona:</div>
                    <div class="info-value">{{ $zona }}</div>
                </td>
                <td style="width: 33%;">
                    <div class="info-label">Punto de venta:</div>
                    <div class="info-value">{{ $pv['nombre'] }}</div>
                </td>
                <td style="width: 33%;">
                    <div class="info-label">Fecha corte</div>
                    <div class="info-value">{{ \Carbon\Carbon::parse($corte->fechaFin)->format('d/m/Y') }}</div>
                </td>
            </tr>
        </table>

        <!-- Cuadrillas -->
        @php
            $granTotalToneladas = 0;
            $granTotalMonto = 0;
        @endphp

        @foreach($pv['cuadrillas'] as $item)
            @php
                $totalToneladasCuadrilla = array_sum(array_column($item['detalle'], 'toneladas'));
                $granTotalToneladas += $totalToneladasCuadrilla;
                $granTotalMonto += $item['cuadrilla']->totalMonto;
            @endphp
            <table class="cuadrilla-table">
                <thead>
                    <tr>
                        <th colspan="3" class="cuadrilla-header">
                            Cuadrilla: {{ $item['cuadrilla']->cuadrillaNombre }}
                        </th>
                    </tr>
                    <tr class="col-headers">
                        <th style="width: 40%">Concepto</th>
                        <th style="width: 30%">Toneladas</th>
                        <th style="width: 30%">Importe(MXN)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($item['detalle'] as $maniobra)
                        <tr>
                            <td>{{ $maniobra['concepto'] }}</td>
                            <td>{{ number_format($maniobra['toneladas'], 3) }}</td>
                            <td>{{ number_format($maniobra['total'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="total-row">
                        <td class="text-right">Total:</td>
                        <td>{{ number_format($totalToneladasCuadrilla, 3) }}</td>
                        <td>{{ number_format($item['cuadrilla']->totalMonto, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endforeach

        <!-- Total General -->
        <table class="total-general-box">
            <tr>
                <td class="total-general-label">Total General:</td>
                <td class="total-general-tons">
                    {{ number_format($granTotalToneladas, 3) }}<br>Toneladas
                </td>
                <td class="total-general-money">
                    ${{ number_format($granTotalMonto, 2) }}<br>MXN
                </td>
            </tr>
        </table>

        <!-- Signature -->
        <table class="signature-area">
            <tr>
                <td style="width: 50%;"></td>
                <td style="width: 50%; text-align: center;">
                    <div class="signature-line"></div>
                    <div class="signature-text">Reviso</div>
                </td>
            </tr>
        </table>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

</body>
</html>
