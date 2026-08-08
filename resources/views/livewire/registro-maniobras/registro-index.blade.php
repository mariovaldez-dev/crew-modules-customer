<div class="space-y-6" wire:init="loadData">
    <!-- Header Page -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Bitácora de maniobras</h1>
            <p class="text-lg text-gray-400 dark:text-gray-500 font-bold tracking-widest mt-1">
                Consulta de operaciones registradas, carga por dispositivo y asignación manual
            </p>
        </div>
        
        <div class="flex gap-2 w-full sm:w-auto justify-end">
            <x-button wire:click="export" variant="secondary" wire:loading.attr="disabled" wire:target="export">
                <i wire:loading.remove wire:target="export" class="fa-solid fa-file-excel text-green-600 dark:text-green-400 mr-2"></i>
                <i wire:loading wire:target="export" class="fa-solid fa-circle-notch fa-spin text-green-600 dark:text-green-400 mr-2"></i>
                Exportar
            </x-button>
            
            @if($this->rolUsuario === 'CO')
                <x-button wire:click="$dispatch('open-nueva-maniobra-modal')" variant="primary">
                    <i class="fa-solid fa-plus mr-2"></i>
                    Alta manual
                </x-button>
            @endif
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="bg-gradient-to-br from-white to-gray-50 dark:from-[#131B20] dark:to-[#171E24] p-6 rounded-[2rem] shadow-sm border border-gray-100 dark:border-white/5 relative">
        <!-- Decoración de fondo sutil contenida -->
        <div class="absolute inset-0 overflow-hidden rounded-[2rem] pointer-events-none">
            <div class="absolute top-0 right-0 -mt-16 -mr-16 w-32 h-32 bg-green-500/5 dark:bg-green-500/10 rounded-full blur-2xl pointer-events-none"></div>
        </div>
        <div class="flex flex-wrap gap-4 items-end relative z-10">
            <!-- Search -->
            <div class="w-full md:w-64 relative">
                <x-label value="Búsqueda" />
                <div class="{{ !$readyToLoad ? 'hidden' : 'relative' }}">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="search" 
                        class="w-full h-12 pl-11 pr-4 bg-white dark:bg-[#131B20] border border-gray-200 dark:border-white/10 rounded-2xl text-sm text-gray-900 dark:text-white focus:border-green-500 dark:focus:border-green-500 focus:ring-0 shadow-sm transition-colors placeholder:text-gray-400 dark:placeholder:text-gray-500" 
                        placeholder="Folio, Cuadrilla...">
                </div>
                <!-- Skeleton Búsqueda -->
                <div class="{{ $readyToLoad ? 'hidden' : 'w-full h-12 bg-gray-200 dark:bg-white/5 rounded-2xl animate-pulse' }}"></div>
            </div>

            <!-- Rango Fechas -->
            <div class="w-full md:w-40">
                <x-label value="Fecha Inicio" />
                <div class="{{ !$readyToLoad ? 'hidden' : 'block' }}">
                    <x-date-input wire:model.live="filters.fechaInicio" />
                </div>
                <!-- Skeleton Fecha -->
                <div class="{{ $readyToLoad ? 'hidden' : 'w-full h-[42px] bg-gray-200 dark:bg-white/5 rounded-xl animate-pulse' }}"></div>
            </div>
            <div class="w-full md:w-40">
                <x-label value="Fecha Fin" />
                <div class="{{ !$readyToLoad ? 'hidden' : 'block' }}">
                    <x-date-input wire:model.live="filters.fechaFin" />
                </div>
                <!-- Skeleton Fecha -->
                <div class="{{ $readyToLoad ? 'hidden' : 'w-full h-[42px] bg-gray-200 dark:bg-white/5 rounded-xl animate-pulse' }}"></div>
            </div>

            <!-- Almacén -->
            <div class="w-full md:w-56">
                <x-label value="Almacén" />
                <div class="{{ !$readyToLoad ? 'hidden' : 'block' }}">
                    @if($readyToLoad)
                        <x-select wire:model.live="filters.almacenId" 
                                  :options="['' => 'Todos los Almacenes'] + $almacenes" 
                                  placeholder="Todos los Almacenes" />
                    @endif
                </div>
                <!-- Skeleton Almacén -->
                <div class="{{ $readyToLoad ? 'hidden' : 'w-full h-[42px] bg-gray-200 dark:bg-white/5 rounded-xl animate-pulse' }}"></div>
            </div>

            <!-- Estado -->
            <div class="w-full md:w-44">
                <x-label value="Estado" />
                <div class="{{ !$readyToLoad ? 'hidden' : 'block' }}">
                    @if($readyToLoad)
                        <x-select wire:model.live="filters.estado" 
                                  :options="['' => 'Todos', 'En proceso' => 'En proceso', 'Liquidada' => 'Liquidada']" 
                                  placeholder="Todos" />
                    @endif
                </div>
                <!-- Skeleton Estado -->
                <div class="{{ $readyToLoad ? 'hidden' : 'w-full h-[42px] bg-gray-200 dark:bg-white/5 rounded-xl animate-pulse' }}"></div>
            </div>

            <!-- Botón Limpiar -->
            <div class="w-full md:w-auto flex items-end">
                <div class="{{ !$readyToLoad ? 'hidden' : 'block' }}">
                    <button wire:click="resetCustomFilters" class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 text-gray-700 dark:text-white shadow-sm transition-all duration-200" title="Limpiar Filtros">
                        <i class="fa-solid fa-eraser text-sm"></i>
                    </button>
                </div>
                <!-- Skeleton Botón Limpiar -->
                <div class="{{ $readyToLoad ? 'hidden' : 'w-12 h-12 bg-gray-200 dark:bg-white/5 rounded-2xl animate-pulse' }}"></div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-green-600 text-white text-[11px] font-bold uppercase tracking-wider">
                        <th class="px-6 py-4">Folio</th>
                        <th class="px-6 py-4">Fecha</th>
                        <th class="px-6 py-4">Tipo</th>
                        <th class="px-6 py-4">Almacen</th>
                        <th class="px-6 py-4">Cuadrilla</th>
                        <th class="px-6 py-4 text-center">Toneladas</th>
                        <th class="px-6 py-4">Doc SAP</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                        <th class="px-6 py-4">Corte</th>
                    </tr>
                </thead>
                <tbody wire:loading.class="hidden" class="{{ !$readyToLoad ? 'hidden' : '' }} divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    @forelse($maniobras as $maniobra)
                        <tr class="group hover:bg-gray-50 dark:hover:bg-white/5 transition-colors duration-200">
                            <!-- Folio -->
                            <td class="px-6 py-4 whitespace-nowrap text-gray-900 dark:text-white">{{ $maniobra->folio }}</td>
                            <!-- Fecha -->
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">{{ $maniobra->fecha->format('d-m-Y') }}</td>
                            <!-- Tipo -->
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">{{ $maniobra->tipoManiobraNombre }}</td>
                            <!-- Almacen -->
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">{{ $maniobra->almacenNombre }}</td>
                            <!-- Cuadrilla -->
                            <td class="px-6 py-4 whitespace-nowrap text-gray-700 dark:text-gray-300">{{ $maniobra->cuadrillaNombre }}</td>
                            <!-- Toneladas -->
                            <td class="px-6 py-4 whitespace-nowrap text-center text-gray-900 dark:text-white">{{ rtrim(rtrim(number_format($maniobra->toneladas, 3, '.', ''), '0'), '.') }}</td>
                            <!-- Doc SAP -->
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">{{ $maniobra->documentoSap ?? '' }}</td>
                            <!-- Estado (Badge) -->
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <x-status-badge :status="$maniobra->estado" />
                            </td>
                            <!-- Corte -->
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400">
                                @if($maniobra->corteId)
                                    LIQ-{{ str_pad($maniobra->corteId, 4, '0', STR_PAD_LEFT) }}
                                @endif
                            </td>
                        </tr>
                    @empty
                        @if($readyToLoad)
                            <tr>
                                <td colspan="9" class="px-6 py-20 text-center text-gray-400 dark:text-gray-500">
                                    <div class="flex flex-col items-center gap-3">
                                        <div class="w-16 h-16 rounded-full bg-gray-50 dark:bg-white/5 flex items-center justify-center text-2xl text-gray-300 dark:text-gray-600 mb-2 border border-gray-100 dark:border-white/5">
                                            <i class="fa-solid fa-inbox"></i>
                                        </div>
                                        <p class="font-bold text-gray-700 dark:text-gray-300">No se encontraron registros</p>
                                        <p class="text-xs max-w-sm">Prueba cambiando los filtros de fecha o usa el buscador para intentar nuevamente.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforelse
                </tbody>
                <tbody wire:loading.class.remove="hidden" class="{{ $readyToLoad ? 'hidden' : '' }} divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    @for($i = 0; $i < 3; $i++)
                        <tr class="animate-pulse">
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-16"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-20"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-16"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-24"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-16"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-12 mx-auto"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-16"></div></td>
                            <td class="px-6 py-4"><div class="h-6 bg-gray-200 dark:bg-white/10 rounded-full w-20 mx-auto"></div></td>
                            <td class="px-6 py-4"><div class="h-4 bg-gray-200 dark:bg-white/10 rounded w-16"></div></td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>
        @if(method_exists($maniobras, 'hasPages') && $maniobras->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5">
                {{ $maniobras->links() }}
            </div>
        @endif
    </div>

    <!-- Modals -->
    @livewire('registro-maniobras.nueva-maniobra-form')
</div>
