<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse min-w-[800px]">
        <thead>
            <tr class="bg-green-600 text-white text-[11px] font-bold uppercase tracking-wider">
                <th class="px-4 py-3 sticky left-0 bg-green-500 dark:bg-green-600 z-10">Cuadrilla</th>
                <th class="px-4 py-3 sticky left-[120px] bg-green-500 dark:bg-green-600 z-10">Punto de Venta</th>
                @foreach($tiposManiobra as $idTipo => $nombreTipo)
                    <th class="px-4 py-3 text-right">{{ $nombreTipo }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
            @foreach($cuadrillas as $cuadrilla)
                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <td class="px-4 py-3.5 whitespace-nowrap sticky left-0 bg-white dark:bg-[#131B20]">
                        <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $cuadrilla['nombreCuadrilla'] ?? 'N/A' }}</div>
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap sticky left-[120px] bg-white dark:bg-[#131B20]">
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-semibold">{{ $cuadrilla['nombrePuntoVenta'] ?? 'N/A' }}</div>
                    </td>
                    
                    @php
                        // Convertir la listaTarifas en un mapa fácil de buscar [idTipo => precio]
                        $tarifasMap = [];
                        if (isset($cuadrilla['listaTarifas'])) {
                            foreach ($cuadrilla['listaTarifas'] as $t) {
                                $tarifasMap[$t['idTipoManiobra']] = $t['tarifa'];
                            }
                        }
                    @endphp

                    @foreach($tiposManiobra as $idTipo => $nombreTipo)
                        <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-semibold text-gray-800 dark:text-white">
                            @if(isset($tarifasMap[$idTipo]))
                                ${{ number_format($tarifasMap[$idTipo], 2) }}
                            @else
                                <span class="text-gray-300 dark:text-gray-600">-</span>
                            @endif
                        </td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
