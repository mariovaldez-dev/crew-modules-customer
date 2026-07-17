<div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
    @foreach($cuadrillas as $cuadrilla)
        @php
            $tarifasMap = [];
            if (isset($cuadrilla['listaTarifas'])) {
                foreach ($cuadrilla['listaTarifas'] as $t) {
                    $tarifasMap[$t['idTipoManiobra']] = $t['tarifa'];
                }
            }
        @endphp
        
        <div class="bg-white dark:bg-[#18232A] border border-gray-100 dark:border-white/5 rounded-3xl p-6 shadow-sm hover:shadow-md transition-shadow relative overflow-hidden group">
            
            {{-- Decoration --}}
            <div class="absolute top-0 right-0 w-32 h-32 bg-green-500/5 dark:bg-green-500/10 rounded-full blur-3xl -mr-10 -mt-10 transition-transform group-hover:scale-110"></div>
            
            <div class="relative">
                <div class="flex items-start justify-between mb-5">
                    <div>
                        <h3 class="text-lg font-black text-gray-900 dark:text-white">{{ $cuadrilla['nombreCuadrilla'] ?? 'N/A' }}</h3>
                        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium">
                            <i class="fa-solid fa-store text-gray-400"></i>
                            {{ $cuadrilla['nombrePuntoVenta'] ?? 'N/A' }}
                        </div>
                    </div>
                    @if(isset($cuadrilla['miembros']))
                        <div class="bg-green-50 dark:bg-green-500/10 text-green-700 dark:text-green-400 text-xs font-bold px-3 py-1.5 rounded-full flex items-center gap-1.5">
                            <i class="fa-solid fa-users"></i> {{ $cuadrilla['miembros'] }}
                        </div>
                    @endif
                </div>
                
                <hr class="border-gray-100 dark:border-white/5 mb-5">
                
                <div class="grid grid-cols-2 gap-3">
                    @foreach($tiposManiobra as $idTipo => $nombreTipo)
                        <div class="bg-gray-50 dark:bg-white/5 rounded-2xl p-3 flex flex-col justify-between">
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
            </div>
        </div>
    @endforeach
</div>
