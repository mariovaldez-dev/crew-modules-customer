<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" wire:init="loadData">
    <!-- Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div>
            <a href="{{ route('corte-liquidacion.index') }}" class="text-sm font-bold text-green-600 hover:text-green-700 flex items-center gap-2 mb-2">
                <i class="fa-solid fa-arrow-left"></i> Volver a Cortes
            </a>
            <h1 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fa-solid fa-file-invoice-dollar text-green-500"></i>
                Detalle del Corte
            </h1>
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
            <div class="p-6 border-b border-gray-100 dark:border-white/5 flex flex-col md:flex-row md:items-center justify-between gap-4 bg-gray-50/50 dark:bg-[#0B1115]/50">
                <div>
                    <h2 class="text-2xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                        Corte {{ $corte['folio'] ?? 'Borrador' }}
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Periodo: <span class="font-bold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($corte['fechaInicio'])->format('d/M/Y') }}</span> 
                        al <span class="font-bold text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($corte['fechaFin'])->format('d/M/Y') }}</span>
                    </p>
                </div>
                
                <div class="flex items-center gap-3">
                    <x-status-badge :status="$corte['estado']" />
                    
                    @if($corte['estado'] === 'Confirmado')
                        <button type="button" @click="$dispatch('open-pdf-modal')" class="inline-flex items-center justify-center font-bold transition-all duration-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-[#131B20] bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-white/10 dark:hover:bg-white/20 dark:text-gray-200 shadow-sm !py-2 !px-4 text-xs">
                            <i class="fa-solid fa-print mr-2"></i>
                            Imprimir PDF
                        </button>
                    @elseif($this->todasConfirmadas)
                        <x-button wire:click="$dispatch('open-modal', 'confirm-corte-general')" variant="primary" class="!py-2 !px-4 text-xs">
                            <i class="fa-solid fa-check-double mr-2"></i>
                            Confirmar Corte
                        </x-button>
                    @else
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            Confirme todas las cuadrillas para habilitar el corte
                        </span>
                    @endif

                    @if($corte['estado'] === 'Borrador')
                        <x-button wire:click="$dispatch('open-modal', 'confirm-regenerar')" variant="secondary" class="!py-2 !px-4 text-xs">
                            <i class="fa-solid fa-rotate-right mr-2"></i>
                            Regenerar
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
                <div class="p-10">
                    <x-table-empty-state message="No hay maniobras registradas en este periodo." />
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left border-collapse">
                        <thead class="text-xs text-gray-500 bg-gray-50/50 dark:bg-white/5 uppercase border-b border-gray-100 dark:border-white/5">
                            <tr>
                                <th class="px-6 py-4 font-black tracking-wider">Cuadrilla</th>
                                <th class="px-6 py-4 font-black tracking-wider">Punto de Venta</th>
                                <th class="px-6 py-4 font-black tracking-wider text-right">Toneladas</th>
                                <th class="px-6 py-4 font-black tracking-wider text-right">Total Pagar</th>
                                <th class="px-6 py-4 font-black tracking-wider text-center">Estatus</th>
                                <th class="px-6 py-4 font-black tracking-wider text-center">Acciones</th>
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
                                        {{ $cuadrilla['almacenId'] }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-medium text-gray-700 dark:text-gray-300">
                                        {{ number_format($cuadrilla['totalToneladas'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                        ${{ number_format($cuadrilla['montoCuadrilla'], 2) }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold {{ $cuadrilla['estaConfirmada'] ? 'bg-green-100 text-green-700 dark:bg-green-500/10 dark:text-green-400' : 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/10 dark:text-yellow-400' }}">
                                            @if($cuadrilla['estaConfirmada'])
                                                <i class="fa-solid fa-check"></i> Confirmada
                                            @else
                                                <i class="fa-regular fa-clock"></i> Pendiente
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
        <!-- Modales -->
        @include('livewire.corte.resumen-maniobras-modal')
        @include('livewire.corte.pdf-preview-modal')

        <!-- Modal Confirmación General -->
        <x-confirm-modal 
            name="confirm-corte-general"
            title="Confirmar Corte de Liquidación"
            message="¿Estás seguro que deseas confirmar el corte general? Una vez confirmado, se asignarán folios formales y no podrás regenerarlo ni modificar las maniobras incluidas."
            confirmAction="confirmarCorteGeneral"
            confirmText="Confirmar Corte"
            variant="primary"
        />

        <!-- Modal Regenerar -->
        <x-confirm-modal 
            name="confirm-regenerar"
            title="Regenerar Corte"
            message="¿Estás seguro que deseas regenerar el corte? Esto recalculará los importes e incluirá nuevas maniobras que cumplan con los criterios de fecha."
            confirmAction="regenerar"
            confirmText="Sí, Regenerar"
            variant="secondary"
        />
    @endif
</div>
