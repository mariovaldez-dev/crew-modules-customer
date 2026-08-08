<div class="px-4 sm:px-6 lg:px-8 py-8 w-full max-w-9xl mx-auto" wire:init="loadData">
    <!-- Header -->
    <div class="sm:flex sm:justify-between sm:items-center mb-8">
        <div>
            <h1 class="text-2xl md:text-3xl font-black text-gray-900 dark:text-white flex items-center gap-3">
                <i class="fa-solid fa-file-invoice-dollar text-green-500"></i>
                Cortes de liquidación
            </h1>
            <p class="text-lg text-gray-400 dark:text-gray-500 font-bold tracking-widest mt-1">Historial de cortes generados</p>
        </div>

        <div class="mt-4 sm:mt-0">
            <x-button wire:click="prepararNuevoCorte" variant="primary">
                <i class="fa-solid fa-plus mr-2"></i> Generar nuevo corte
            </x-button>
        </div>
    </div>

    <!-- Lista -->
    <div class="bg-white dark:bg-[#131B20] rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 overflow-hidden">
        @if($cargando)
            <div class="p-10 flex flex-col items-center justify-center text-gray-400">
                <i class="fa-solid fa-circle-notch fa-spin text-3xl mb-3 text-green-500"></i>
                <p class="font-bold tracking-wide">Cargando historial...</p>
            </div>
        @elseif(empty($cortes))
            <x-table-empty-state message="No hay cortes de liquidación registrados en esta zona." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-500 bg-gray-50/50 dark:bg-white/5 uppercase border-b border-gray-100 dark:border-white/5">
                        <tr>
                            <th class="px-6 py-4 font-black tracking-wider">Folio</th>
                            <th class="px-6 py-4 font-black tracking-wider">Periodo</th>
                            <th class="px-6 py-4 font-black tracking-wider text-right">Toneladas Totales</th>
                            <th class="px-6 py-4 font-black tracking-wider text-right">Monto Total</th>
                            <th class="px-6 py-4 font-black tracking-wider text-center">Estado</th>
                            <th class="px-6 py-4 font-black tracking-wider text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($cortes as $c)
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition-colors group">
                                <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">
                                    {{ $c['folio'] ?? 'Borrador' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($c['fechaInicio'])->format('d/M/Y') }}</span>
                                    <span class="text-gray-400 mx-1">-</span>
                                    <span class="text-gray-700 dark:text-gray-300">{{ \Carbon\Carbon::parse($c['fechaFin'])->format('d/M/Y') }}</span>
                                </td>
                                <td class="px-6 py-4 text-right font-medium text-gray-700 dark:text-gray-300">
                                    {{ number_format($c['toneladasTotal'], 3) }}
                                </td>
                                <td class="px-6 py-4 text-right font-bold text-gray-900 dark:text-white">
                                    ${{ number_format($c['montoTotal'], 2) }}
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <x-status-badge :status="$c['estado']" />
                                </td>
                                <td class="px-6 py-4 text-center">
                                    <a href="{{ route('corte-liquidacion.detalle', ['id' => $c['id']]) }}" class="inline-flex items-center justify-center font-bold transition-all duration-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-[#131B20] bg-gray-100 hover:bg-gray-200 text-gray-700 dark:bg-white/10 dark:hover:bg-white/20 dark:text-gray-200 shadow-sm !py-1.5 !px-3 text-xs">
                                        <i class="fa-solid fa-eye mr-2"></i> Ver Detalles
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
                            wire:model.live="fechaInicio" 
                            disabled="{{ !$isPrimerCorte }}"
                        />
                        @error('fechaInicio') <span class="text-xs text-red-500 mt-2 block">{{ $message }}</span> @enderror
                    </div>
                    <div class="flex-1 w-full text-left">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider mb-2">Fecha Fin</label>
                        <x-date-input 
                            wire:model.live="fechaFin" 
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
</div>
