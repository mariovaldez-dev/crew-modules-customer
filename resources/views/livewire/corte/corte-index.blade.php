<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" wire:init="loadData">
    <!-- Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8 bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-2xl p-4 shadow-sm">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 bg-teal-50 dark:bg-teal-950/30 rounded-2xl flex items-center justify-center text-teal-600 dark:text-teal-400 shrink-0">
                <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
            </div>
            <div>
                <h1 class="text-2xl font-black tracking-tight text-gray-900 dark:text-white">Cortes de liquidación</h1>
                <p class="text-sm text-gray-400 dark:text-gray-500 font-medium mt-0.5">Historial de cortes generados y administración de liquidaciones</p>
            </div>
        </div>

        <div class="mt-4 sm:mt-0">
            <x-button @click="$dispatch('open-modal', 'nuevo-corte-modal')" variant="primary">
                <i class="fa-solid fa-plus mr-2"></i> Generar nuevo corte
            </x-button>
        </div>
    </div>

    <!-- Lista -->
    <div class="bg-white dark:bg-[#131B20] rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 overflow-hidden">
        @if(!$cargando && empty($cortes))
            <x-table-empty-state message="No hay cortes de liquidación registrados en esta zona." />
        @else
            <div class="overflow-x-auto custom-scrollbar">
                <table class="w-full min-w-[700px] text-[13px] text-left">
                    <thead class="text-[11px] text-white bg-green-600 uppercase border-b border-gray-100 dark:border-white/5 font-bold tracking-wider">
                        <tr>
                            <th class="px-4 py-3 whitespace-nowrap">Folio</th>
                            <th class="px-4 py-3 whitespace-nowrap">Periodo</th>
                            <th class="px-4 py-3 text-right whitespace-nowrap">Toneladas Totales</th>
                            <th class="px-4 py-3 text-right whitespace-nowrap">Monto Total</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Estado</th>
                            <th class="px-4 py-3 text-center whitespace-nowrap">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @if($cargando)
                            @for($i = 0; $i < 4; $i++)
                                <tr class="animate-pulse">
                                    <td class="px-4 py-3"><div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-20"></div></td>
                                    <td class="px-4 py-3"><div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-36"></div></td>
                                    <td class="px-4 py-3"><div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-16 ml-auto"></div></td>
                                    <td class="px-4 py-3"><div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-20 ml-auto"></div></td>
                                    <td class="px-4 py-3"><div class="h-5 bg-gray-200 dark:bg-white/10 rounded-full w-24 mx-auto"></div></td>
                                    <td class="px-4 py-3"><div class="h-7 bg-gray-200 dark:bg-white/10 rounded-xl w-28 mx-auto"></div></td>
                                </tr>
                            @endfor
                        @else
                            @foreach($cortesList as $c)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                                    <td class="px-4 py-3 font-bold text-gray-900 dark:text-white">
                                        {{ $c['folio'] ?? 'Borrador' }}
                                    </td>
                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ \Carbon\Carbon::parse($c['fechaInicio'])->format('d/m/Y') }}</span>
                                        <span class="text-gray-400 mx-1">-</span>
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ \Carbon\Carbon::parse($c['fechaFin'])->format('d/m/Y') }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                        {{ number_format($c['toneladasTotal'], 3) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white whitespace-nowrap">
                                        ${{ number_format($c['montoTotal'], 2) }}
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <x-status-badge :status="$c['estado']" />
                                    </td>
                                    <td class="px-4 py-3 text-center whitespace-nowrap">
                                        <a href="{{ route('corte-liquidacion.detalle', ['id' => $c['corteId'] ?? $c['id']]) }}" class="inline-flex items-center justify-center font-bold transition-all duration-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-[#131B20] bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-white/10 dark:hover:bg-white/20 dark:text-gray-200 shadow-sm !py-1.5 !px-3 text-xs">
                                            <i class="fa-solid fa-eye mr-2"></i> Ver Detalles
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                </table>
            </div>

            @if(!$cargando && method_exists($cortesList, 'hasPages') && $cortesList->hasPages())
                <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5 bg-gray-50/30 dark:bg-white/2">
                    {{ $cortesList->links('components.pagination') }}
                </div>
            @endif
        @endif
    </div>

    <!-- Modal Nuevo Corte -->
    <x-modal name="nuevo-corte-modal" title="Generar Nuevo Corte" maxWidth="2xl">
        <form wire:submit="generarCorte">
            <div class="p-6">
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
                    Selecciona el rango de fechas para recopilar las maniobras pendientes.
                </p>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 w-full text-left">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Fecha Inicio</label>
                        <x-date-input 
                            wire:model.change="fechaInicio" 
                            :max="date('Y-m-d')"
                        />
                        @error('fechaInicio') <span class="text-xs text-red-500 mt-2 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex-1 w-full text-left">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Fecha Fin</label>
                        <x-date-input 
                            wire:model.change="fechaFin" 
                            :max="date('Y-m-d')"
                        />
                        @error('fechaFin') <span class="text-xs text-red-500 mt-2 block">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
            
            <div class="px-6 py-4 bg-gray-50 dark:bg-[#0B1115] border-t border-gray-100 dark:border-white/5 flex justify-end gap-3 rounded-b-3xl shrink-0">
                <x-button type="button" @click="show = false" variant="secondary">Cancelar</x-button>
                <x-button type="submit" variant="primary" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="generarCorte">
                        <i class="fa-solid fa-check mr-2"></i> Generar
                    </span>
                    <span wire:loading wire:target="generarCorte">
                        <i class="fa-solid fa-circle-notch fa-spin mr-2"></i> Procesando...
                    </span>
                </x-button>
            </div>
        </form>
    </x-modal>

    @include('livewire.corte.reemplazar-borrador-modal')
</div>
