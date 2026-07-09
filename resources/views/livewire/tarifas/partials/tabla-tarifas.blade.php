<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse min-w-[800px]">
        <thead>
            <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5">
                <th class="px-4 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider sticky left-0 bg-gray-50/50 dark:bg-[#131B20]">Cuadrilla / PV</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Carga 25kg</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Carga 50kg</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Descarga 25kg</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Descarga 50kg</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Traslado</th>
                <th class="px-4 py-3 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider text-right">Apaleo</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
            @foreach($cuadrillas as $cuadrilla)
                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                    <td class="px-4 py-3.5 whitespace-nowrap sticky left-0 bg-white dark:bg-[#131B20]">
                        <div class="text-sm font-bold text-gray-900 dark:text-white">{{ $cuadrilla->nombre }}</div>
                        <div class="text-xs text-gray-500 dark:text-gray-400 font-semibold">{{ $puntosVenta[$cuadrilla->puntoVentaId] ?? 'N/A' }}</div>
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-semibold text-gray-800 dark:text-white">
                        ${{ number_format($cuadrilla->tarifas->carga25, 2) }}
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-semibold text-gray-800 dark:text-white">
                        ${{ number_format($cuadrilla->tarifas->carga50, 2) }}
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-semibold text-gray-800 dark:text-white">
                        ${{ number_format($cuadrilla->tarifas->descarga25, 2) }}
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-semibold text-gray-800 dark:text-white">
                        ${{ number_format($cuadrilla->tarifas->descarga50, 2) }}
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-semibold text-gray-800 dark:text-white">
                        ${{ number_format($cuadrilla->tarifas->traslado, 2) }}
                    </td>
                    <td class="px-4 py-3.5 whitespace-nowrap text-right font-mono font-semibold text-gray-800 dark:text-white">
                        ${{ number_format($cuadrilla->tarifas->apaleo, 2) }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
