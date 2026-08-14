<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Corte de Liquidación {{ $corte['folio'] ?? '' }}</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            border-bottom: 2px solid #16a34a;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header table {
            width: 100%;
            border: none;
        }
        .header td {
            vertical-align: top;
        }
        .title {
            font-size: 24px;
            font-weight: bold;
            color: #16a34a;
            margin: 0 0 5px 0;
        }
        .subtitle {
            font-size: 14px;
            color: #666;
            margin: 0;
        }
        .meta-info {
            text-align: right;
            font-size: 12px;
        }
        .meta-info p {
            margin: 2px 0;
        }
        
        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 25px;
        }
        .summary-box table {
            width: 100%;
        }
        .summary-box th {
            text-align: left;
            font-size: 11px;
            text-transform: uppercase;
            color: #64748b;
            padding-bottom: 5px;
        }
        .summary-box td {
            font-size: 18px;
            font-weight: bold;
            color: #0f172a;
        }
        .summary-box .amount {
            color: #16a34a;
        }

        .cuadrilla-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .cuadrilla-title {
            background-color: #16a34a;
            color: white;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            border-radius: 4px 4px 0 0;
            margin: 0;
        }
        .cuadrilla-meta {
            background-color: #f0fdf4;
            border-left: 1px solid #bbf7d0;
            border-right: 1px solid #bbf7d0;
            padding: 8px 12px;
            font-size: 11px;
            color: #166534;
            font-weight: bold;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
        }
        .data-table th, .data-table td {
            border: 1px solid #e2e8f0;
            padding: 8px 10px;
            font-size: 11px;
        }
        .data-table th {
            background-color: #f8fafc;
            color: #475569;
            text-transform: uppercase;
            font-size: 10px;
            text-align: left;
        }
        .data-table .text-right {
            text-align: right;
        }
        .data-table .text-center {
            text-align: center;
        }
        .data-table .font-bold {
            font-weight: bold;
        }
        .data-table .total-row td {
            background-color: #f8fafc;
            font-weight: bold;
            font-size: 12px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #94a3b8;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }
        
        .page-number:after {
            content: counter(page);
        }
    </style>
</head>
<body>

    <div class="footer">
        <p>Corte de Liquidación {{ $corte['folio'] ?? 'Sin Folio' }} generado el {{ date('d/m/Y H:i') }} - Página <span class="page-number"></span></p>
    </div>

    <div class="header">
        <table>
            <tr>
                <td>
                    <h1 class="title">Corte de Liquidación</h1>
                    <p class="subtitle">Grupo Impulsora - Módulo de Operaciones</p>
                </td>
                <td class="meta-info">
                    <p><strong>Folio:</strong> {{ $corte['folio'] ?? 'Borrador' }}</p>
                    <p><strong>Estado:</strong> {{ $corte['estado'] ?? 'Desconocido' }}</p>
                    <p><strong>Zona:</strong> {{ $zona }}</p>
                    <p><strong>Periodo:</strong> {{ \Carbon\Carbon::parse($corte['fechaInicio'])->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($corte['fechaFin'])->format('d/m/Y') }}</p>
                </td>
            </tr>
        </table>
    </div>

    <div class="summary-box">
        <table>
            <tr>
                <th>Total Maniobras</th>
                <th>Total Toneladas</th>
                <th>Total a Pagar</th>
            </tr>
            <tr>
                <td>
                    @php
                        $totalManiobras = 0;
                        if(!empty($corte['cuadrillas'])) {
                            foreach($corte['cuadrillas'] as $c) {
                                $totalManiobras += $c['totalManiobras'] ?? 0;
                            }
                        }
                    @endphp
                    {{ $totalManiobras }}
                </td>
                <td>{{ number_format($corte['toneladasTotal'], 3) }} Ton</td>
                <td class="amount">${{ number_format($corte['montoTotal'], 2) }} MXN</td>
            </tr>
        </table>
    </div>

    @if(!empty($corte['cuadrillas']))
        @php
            $cuadrillasPorPV = collect($corte['cuadrillas'])->groupBy('almacenId');
        @endphp

        @foreach($cuadrillasPorPV as $pvId => $cuadrillasDelPv)
            @php
                $primeraCuadrilla = $cuadrillasDelPv->first();
                $nombrePv = $primeraCuadrilla['almacenNombre'] ?? $pvId;
            @endphp
            <div style="margin-top: 20px; border-bottom: 2px solid #e2e8f0; margin-bottom: 15px;">
                <h2 style="font-size: 16px; color: #0f172a; margin: 0 0 10px 0;">Punto de Venta: {{ $nombrePv }}</h2>
            </div>
            
            @foreach($cuadrillasDelPv as $cuadrilla)
                <div class="cuadrilla-section">
                    <h3 class="cuadrilla-title">{{ $cuadrilla['nombreCuadrilla'] }}</h3>
                    <div class="cuadrilla-meta">
                        Toneladas Totales: {{ number_format($cuadrilla['totalToneladas'], 3) }}
                        | Total a Pagar: ${{ number_format($cuadrilla['montoCuadrilla'], 2) }}
                    </div>
                    
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th width="15%">Fecha</th>
                                <th width="40%">Concepto de Maniobra</th>
                                <th width="15%" class="text-right">Toneladas</th>
                                <th width="15%" class="text-right">Tarifa</th>
                                <th width="15%" class="text-right">Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(!empty($cuadrilla['maniobrasDetalle']))
                                @php
                                    $maniobras = is_string($cuadrilla['maniobrasDetalle']) 
                                        ? json_decode($cuadrilla['maniobrasDetalle'], true) 
                                        : $cuadrilla['maniobrasDetalle'];
                                @endphp
                                
                                @foreach($maniobras as $maniobra)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($maniobra['fecha'])->format('d/m/Y') }}</td>
                                        <td>{{ $maniobra['concepto'] }}</td>
                                        <td class="text-right">{{ number_format($maniobra['toneladas'], 3) }}</td>
                                        <td class="text-right">${{ number_format($maniobra['tarifaAplicada'], 2) }}</td>
                                        <td class="text-right font-bold">${{ number_format($maniobra['montoTotal'], 2) }}</td>
                                    </tr>
                                @endforeach
                            @else
                                <tr>
                                    <td colspan="5" class="text-center">No hay detalle de maniobras</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td colspan="2" class="text-right">Total Cuadrilla:</td>
                                <td class="text-right">{{ number_format($cuadrilla['totalToneladas'], 3) }}</td>
                                <td></td>
                                <td class="text-right">${{ number_format($cuadrilla['montoCuadrilla'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            @endforeach
        @endforeach
    @else
        <p style="text-align: center; color: #666; margin-top: 40px;">No hay cuadrillas registradas en este corte.</p>
    @endif

</body>
</html>
