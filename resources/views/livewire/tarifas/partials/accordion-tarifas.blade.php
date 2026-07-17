<div class="overflow-hidden border border-gray-100 dark:border-white/5 rounded-3xl bg-white dark:bg-[#18232A] shadow-sm">
    {{-- Header --}}
    <div class="grid grid-cols-12 gap-4 bg-green-600 dark:bg-green-700 px-6 py-4 text-[11px] font-bold text-white uppercase tracking-wider">
        <div class="col-span-6 md:col-span-5">Cuadrilla</div>
        <div class="col-span-5 md:col-span-6">Punto de Venta</div>
        <div class="col-span-1 text-right">Tarifas</div>
    </div>
    
    {{-- Body --}}
    <div class="space-y-3 p-3">
        @foreach($cuadrillas as $cuadrilla)
            @php
                $tarifasMap = [];
                if (isset($cuadrilla['listaTarifas'])) {
                    foreach ($cuadrilla['listaTarifas'] as $t) {
                        $tarifasMap[$t['idTipoManiobra']] = $t['tarifa'];
                    }
                }
            @endphp
            
            <div x-data="{ open: false }" 
                 wire:key="tarifa-cuadrilla-{{ isset($cuadrilla['idCuadrilla']) ? $cuadrilla['idCuadrilla'] : $loop->index }}"
                 class="group border rounded-2xl overflow-hidden transition-all duration-200"
                 :class="open ? 'border-green-500 dark:border-green-500/50 shadow-md ring-1 ring-green-500/20' : 'border-gray-100 dark:border-white/5 bg-white dark:bg-[#131B20] hover:border-green-200 dark:hover:border-green-500/30'">
                 
                {{-- Fila principal clickeable --}}
                <div @click="open = !open" 
                     class="grid grid-cols-12 gap-4 px-5 py-4 items-center cursor-pointer transition-colors"
                     :class="open ? 'bg-green-50/50 dark:bg-green-500/10' : 'hover:bg-green-50/30 dark:hover:bg-green-500/5'">
                    
                    <div class="col-span-6 md:col-span-5 font-bold text-sm text-gray-900 dark:text-white flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center transition-colors"
                             :class="open ? 'bg-green-500 text-white shadow-sm shadow-green-500/30' : 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 group-hover:bg-green-100 dark:group-hover:bg-green-500/20'">
                            <i class="fa-solid fa-users text-xs"></i>
                        </div>
                        {{ $cuadrilla['nombreCuadrilla'] ?? 'N/A' }}
                    </div>
                    <div class="col-span-5 md:col-span-6 text-sm text-gray-500 dark:text-gray-400 flex items-center">
                        <i class="fa-solid fa-store mr-2 opacity-50" :class="open ? 'text-green-600 dark:text-green-400 opacity-100' : ''"></i>
                        <span :class="open ? 'text-gray-700 dark:text-gray-200 font-medium' : ''">{{ $cuadrilla['nombrePuntoVenta'] ?? 'N/A' }}</span>
                    </div>
                    <div class="col-span-1 text-right text-gray-400">
                        <div class="inline-flex w-8 h-8 rounded-full items-center justify-center transition-colors"
                             :class="open ? 'bg-green-100 dark:bg-green-500/20 text-green-600 dark:text-green-400' : 'group-hover:bg-gray-100 dark:group-hover:bg-white/10'">
                            <i class="fa-solid fa-chevron-down transition-transform duration-300" :class="open ? 'rotate-180' : ''"></i>
                        </div>
                    </div>
                </div>
                
                {{-- Contenido desplegable (Acordeón de fila) --}}
                <div x-show="open" x-collapse>
                    <div class="px-5 py-5 bg-white dark:bg-[#18232A] border-t border-gray-100 dark:border-white/5">
                        <p class="text-xs font-bold text-green-600 dark:text-green-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i class="fa-solid fa-money-bill-wave"></i> Desglose de Tarifas
                        </p>
                        
                        @if(empty($tarifasMap))
                            <div class="bg-gray-50 dark:bg-[#131B20] border border-gray-100/50 dark:border-white/5 rounded-2xl p-8 text-center flex flex-col items-center justify-center">
                                <div class="w-12 h-12 bg-gray-100 dark:bg-white/5 text-gray-400 dark:text-gray-500 rounded-full flex items-center justify-center mb-3">
                                    <i class="fa-solid fa-clipboard-question text-xl"></i>
                                </div>
                                <h4 class="text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Sin tarifas asignadas</h4>
                                <p class="text-xs text-gray-500 dark:text-gray-400 max-w-sm">Esta cuadrilla actualmente no tiene ninguna tarifa de maniobra configurada.</p>
                            </div>
                        @else
                            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                                @foreach($tiposManiobra as $idTipo => $nombreTipo)
                                    <div class="bg-gray-50 dark:bg-[#131B20] border border-gray-100/50 dark:border-white/5 rounded-2xl p-3 flex flex-col justify-between hover:border-green-200 dark:hover:border-green-500/30 transition-colors">
                                        <span class="text-[10px] uppercase font-bold text-gray-500 dark:text-gray-400 mb-1 tracking-wider">{{ $nombreTipo }}</span>
                                        @if(isset($tarifasMap[$idTipo]))
                                            <span class="font-mono font-black text-green-700 dark:text-green-400 text-sm">
                                                ${{ number_format($tarifasMap[$idTipo], 2) }}
                                            </span>
                                        @else
                                            <span class="font-mono font-medium text-gray-400 dark:text-gray-600 text-sm">-</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
