<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" wire:init="loadData">
    <!-- Header -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-2xl p-4 shadow-sm">
        <div class="flex items-center gap-3 sm:gap-4">
            <!-- Botón Volver (Izquierda) -->
            <a href="{{ route('corte-liquidacion.index') }}" 
               class="w-10 h-10 rounded-xl bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-600 dark:text-gray-300 flex items-center justify-center transition-all duration-200 shrink-0 border border-gray-200/50 dark:border-white/5 shadow-sm group" 
               title="Volver a Cortes">
                <i class="fa-solid fa-arrow-left text-sm group-hover:-translate-x-0.5 transition-transform"></i>
            </a>

            <!-- Icon Box -->
            <div class="w-12 h-12 bg-teal-50 dark:bg-teal-950/30 rounded-2xl flex items-center justify-center text-teal-600 dark:text-teal-400 shrink-0">
                <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
            </div>

            <!-- Título y Subtítulo -->
            <div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">Detalle del Corte</h1>
                <p class="text-sm text-gray-400 dark:text-gray-500 font-medium mt-0.5">Información detallada y resumen por cuadrilla</p>
            </div>
        </div>
    </div>

    @if($cargando)
        <div class="bg-white dark:bg-[#131B20] p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 animate-pulse flex flex-col gap-4">
            <div class="h-6 bg-gray-200 dark:bg-gray-700 rounded w-1/4"></div>
            <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
            <div class="h-10 bg-gray-200 dark:bg-gray-700 rounded w-full mt-4"></div>
        </div>
    @elseif(!$corte)
        <x-table-empty-state message="No se encontró el corte solicitado." />
    @else
        <div class="bg-white dark:bg-[#131B20] rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 overflow-hidden">
            <!-- Header del Corte -->
            <div class="p-6 border-b border-gray-100 dark:border-white/5 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                        Corte {{ $corte['folio'] ?? 'Borrador' }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Periodo: <span class="font-bold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($corte['fechaInicio'])->format('d/m/Y') }}</span> 
                        al <span class="font-bold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($corte['fechaFin'])->format('d/m/Y') }}</span>
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-status-badge :status="$corte['estado']" :solid="false" />
                    
                    @if($corte['estado'] === 'Confirmado')
                        <button type="button" @click="$dispatch('open-pdf-modal')" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold transition-all duration-150 bg-white text-rose-700 border border-white shadow-sm hover:bg-gray-100 cursor-pointer">
                            <i class="fa-solid fa-file-pdf text-xs"></i>
                            Imprimir PDF
                        </button>
                    @elseif($this->tieneAlMenosUnaConfirmada)
                        <x-button wire:click="abrirConfirmarGeneral" variant="primary" class="!py-2 !px-4 text-xs font-bold">
                            <i class="fa-solid fa-check-double mr-1.5"></i>
                            Confirmar Corte
                        </x-button>
                    @else
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            Confirme al menos una cuadrilla para habilitar el corte
                        </span>
                    @endif

                    @if($corte['estado'] === 'Borrador')
                        <x-button wire:click="abrirRegenerar" variant="secondary" class="!py-2.5 !px-4 text-xs gap-2">
                            <i class="fa-solid fa-rotate-right mr-1.5"></i>
                            Regenerar
                        </x-button>
                        <x-button wire:click="abrirEliminarBorrador" variant="danger" class="!py-2.5 !px-4 text-xs gap-2" title="Eliminar Borrador">
                            <i class="fa-solid fa-trash-can"></i>
                        </x-button>
                    @endif
                </div>
            </div>

            <!-- Resumen Estadístico -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-px bg-gray-100 dark:bg-white/5 border-b border-gray-100 dark:border-white/5">
                <div class="bg-white dark:bg-[#131B20] p-6 text-center">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Cuadrillas</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white">{{ count($corte['cuadrillas'] ?? []) }}</p>
                </div>
                <div class="bg-white dark:bg-[#131B20] p-6 text-center">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Maniobras Totales</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white">
                        {{ collect($corte['cuadrillas'] ?? [])->sum('totalManiobras') }}
                    </p>
                </div>
                <div class="bg-white dark:bg-[#131B20] p-6 text-center">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Toneladas</p>
                    <p class="text-2xl font-black text-gray-900 dark:text-white">{{ number_format($corte['toneladasTotal'], 2) }}</p>
                </div>
                <div class="bg-white dark:bg-[#131B20] p-6 text-center">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider mb-1">Monto Total</p>
                    <p class="text-2xl font-black text-green-600 dark:text-green-400">${{ number_format($corte['montoTotal'], 2) }}</p>
                </div>
            </div>

            <!-- Tabla de Cuadrillas -->
            @if(empty($corte['cuadrillas']))
                <x-table-empty-state message="No hay cuadrillas registradas en este corte." />
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="bg-green-600 text-white text-[11px] font-black uppercase tracking-wider border-b border-gray-100 dark:border-white/5">
                            <tr>
                                <th class="px-6 py-4">Cuadrilla</th>
                                <th class="px-6 py-4">Punto de Venta</th>
                                <th class="px-6 py-4 text-right">Toneladas</th>
                                <th class="px-6 py-4 text-right">Total Pagar</th>
                                <th class="px-6 py-4 text-center">Estatus</th>
                                <th class="px-6 py-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach($corte['cuadrillas'] as $index => $cuadrilla)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                                    <td class="px-6 py-4 font-bold text-gray-900 dark:text-white flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-green-50 dark:bg-green-950/40 border border-green-100/50 dark:border-green-800/30 flex items-center justify-center font-bold text-xs text-green-700 dark:text-green-400 shrink-0">
                                            {{ substr(trim($cuadrilla['nombreCuadrilla']), 0, 1) }}
                                        </div>
                                        {{ $cuadrilla['nombreCuadrilla'] }}
                                    </td>
                                    <td class="px-6 py-4 text-gray-600 dark:text-gray-400 font-semibold text-xs">
                                        {{ $cuadrilla['almacenNombre'] ?? $cuadrilla['puntoVentaNombre'] ?? $cuadrilla['almacenId'] }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-gray-700 dark:text-gray-300">
                                        {{ number_format($cuadrilla['totalToneladas'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                        ${{ number_format($cuadrilla['montoCuadrilla'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold border {{ $cuadrilla['estaConfirmada'] ? 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border-emerald-500/30 shadow-sm shadow-emerald-500/10' : 'bg-amber-500/15 text-amber-800 dark:text-amber-300 border-amber-500/30 shadow-sm shadow-amber-500/10' }}">
                                            @if($cuadrilla['estaConfirmada'])
                                                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Confirmada
                                            @else
                                                <span class="h-1.5 w-1.5 rounded-full bg-amber-500"></span> Pendiente
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <button 
                                            wire:click="abrirResumenManiobras({{ $index }})"
                                            class="inline-flex items-center justify-center w-8 h-8 rounded-xl bg-gray-100 text-gray-500 hover:bg-gray-200 hover:text-gray-700 dark:bg-white/5 dark:hover:bg-white/10 dark:text-gray-400 dark:hover:text-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-green-500/50"
                                            title="Ver resumen de maniobras"
                                        >
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif

    <!-- Modales -->
    @include('livewire.corte.resumen-maniobras-modal')
    @include('livewire.corte.pdf-preview-modal')
    @include('livewire.corte.confirmar-corte-modal')
    @include('livewire.corte.regenerar-corte-modal')
    @include('livewire.corte.eliminar-corte-modal')
</div>
