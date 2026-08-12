<div class="space-y-6" wire:init="loadData" x-on:cuadrilla-saved.window="$wire.refreshData()">
    <!-- Header Page -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Cuentas de cuadrillas</h1>
            <p class="text-lg text-gray-400 dark:text-gray-500 font-bold tracking-widest mt-1">
                Administración de equipos de trabajo y asignación de puntos de venta
            </p>
        </div>
        
        @if($this->rolUsuario === 'CO')
        <div>
            <button @click="$dispatch('open-cuadrilla-modal')" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-green-600 hover:bg-green-700 text-white text-sm font-bold shadow-md shadow-green-900/10 hover:shadow-lg transition-all duration-200">
                <i class="fa-solid fa-plus text-xs"></i>
                Nueva cuadrilla
            </button>
        </div>
        @endif
    </div>

    <!-- Actions and Filters bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            <!-- Search -->
            <div class="w-full sm:w-72">
                @if($readyToLoad)
                    <x-label value="Buscar" />
                    <div class="relative w-full">
                        <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                            <i class="fa-solid fa-magnifying-glass text-sm"></i>
                        </span>
                        <input wire:model.live.debounce.300ms="search" type="search" 
                            class="w-full h-12 pl-12 pr-4 bg-white dark:bg-[#1E293B] border border-gray-200 dark:border-white/10 rounded-2xl text-sm text-gray-900 dark:text-white focus:border-green-500 dark:focus:border-green-500 focus:ring-0 shadow-sm transition-colors placeholder:text-gray-400 dark:placeholder:text-gray-500" 
                            placeholder="Busqueda"
                        />  
                    </div>
                @else
                    <div class="h-6 w-24 mb-2 rounded-lg bg-gray-200 dark:bg-white/10 animate-pulse"></div>
                    <div class="h-12 w-full rounded-2xl bg-gray-200 dark:bg-white/10 animate-pulse"></div>
                @endif
            </div>

            @if($this->rolUsuario !== 'AM')
            <!-- Filter Punto de Venta -->
            <div class="w-full sm:w-64">
                @if($readyToLoad)
                    <x-label value="Punto de Venta" />
                    <x-select wire:model.live="filters.puntoVentaId"
                              :options="['' => 'Todos los Puntos de Venta'] + $puntosVenta"
                              placeholder="Todos los Puntos de Venta" />
                @else
                    <div class="h-6 w-24 mb-2 rounded-lg bg-gray-200 dark:bg-white/10 animate-pulse"></div>
                    <div class="h-12 w-full rounded-2xl bg-gray-200 dark:bg-white/10 animate-pulse"></div>
                @endif
            </div>
            @endif

            <!-- Filter Lider -->
            <div class="w-full sm:w-64">
                @if($readyToLoad)
                    <x-label value="Líder" />
                    <x-select wire:model.live="filters.lider"
                              :options="['' => 'Todos los Líderes'] + (is_array($lideres) && array_is_list($lideres) ? array_combine($lideres, $lideres) : $lideres)"
                              placeholder="Todos los Líderes" />
                @else
                    <div class="h-6 w-24 mb-2 rounded-lg bg-gray-200 dark:bg-white/10 animate-pulse"></div>
                    <div class="h-12 w-full rounded-2xl bg-gray-200 dark:bg-white/10 animate-pulse"></div>
                @endif
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-green-600 text-white text-[11px] font-bold uppercase tracking-wider">
                        <th class="px-6 py-4">Cuadrilla</th>
                        <th class="px-6 py-4">Líder</th>
                        <th class="px-6 py-4 text-center">Miembros</th>
                        <th class="px-6 py-4">Punto de Venta</th>
                        @if($this->rolUsuario === 'CO')
                            <th class="px-6 py-4 text-right">Acción</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    {{-- Filas de datos --}}
                    @forelse($cuadrillas as $cuadrilla)
                        <tr wire:loading.class.add="hidden" wire:target="loadData,refreshData,delete" class="hover:bg-gray-50/40 dark:hover:bg-white/5 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500/10 to-emerald-500/10 dark:from-emerald-500/20 dark:to-teal-500/10 border border-green-500/20 text-green-700 dark:text-green-400 shadow-sm shrink-0 flex items-center justify-center font-bold text-xs">
                                        {{ substr(trim($cuadrilla['nombre']), 0, 1) }}
                                    </div>
                                    <div class="font-bold text-gray-900 dark:text-white">
                                        {{ $cuadrilla['nombre'] }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400 font-medium">
                                {{ $cuadrilla['lider'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-bold text-gray-800 dark:text-white">
                                {{ $cuadrilla['miembros'] }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400 font-semibold text-xs">
                                {{ $puntosVenta[$cuadrilla['puntoVentaId']] ?? $cuadrilla['puntoVentaId'] }}
                            </td>
                            @if($this->rolUsuario === 'CO')
                            <td class="px-6 py-4 whitespace-nowrap text-right space-x-1">
                                <!-- Tooltip Editar -->
                                <div class="relative group inline-block">
                                    <button @click="$dispatch('open-cuadrilla-modal', { cuadrilla: {{ json_encode($cuadrilla) }} })"
                                            class="text-green-600 dark:text-green-400 transition-all duration-200 p-2.5 rounded-xl hover:bg-green-50 dark:hover:bg-green-950/30">
                                        <i class="fa-solid fa-pen-to-square text-base"></i>
                                    </button>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-900 dark:bg-gray-800 text-white text-[10px] py-1 px-2 rounded-lg font-bold whitespace-nowrap shadow-md z-10 pointer-events-none">
                                        Editar
                                    </div>
                                </div>

                                <!-- Tooltip Eliminar -->
                                <div class="relative group inline-block">
                                    <button wire:click="confirmDelete({{ $cuadrilla['id'] }})"
                                            class="text-red-600 dark:text-red-400 transition-all duration-200 p-2.5 rounded-xl hover:bg-red-50 dark:hover:bg-red-950/30">
                                        <i class="fa-solid fa-trash-can text-base"></i>
                                    </button>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-900 dark:bg-gray-800 text-white text-[10px] py-1 px-2 rounded-lg font-bold whitespace-nowrap shadow-md z-10 pointer-events-none">
                                        Eliminar
                                    </div>
                                </div>
                            </td>
                            @endif
                        </tr>
                    @empty
                        @if($readyToLoad)
                            <tr wire:loading.class.add="hidden" wire:target="loadData,refreshData,delete">
                                <td colspan="{{ $this->rolUsuario === 'CO' ? 5 : 4 }}" class="px-6 py-16 text-center text-gray-400 dark:text-gray-500">
                                    <div class="flex flex-col items-center gap-3 max-w-sm mx-auto p-6 bg-gray-50/50 dark:bg-white/5 rounded-3xl border border-gray-100/50 dark:border-white/5 shadow-sm">
                                        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-400">
                                            <i class="fa-regular fa-folder-open text-xl"></i>
                                        </div>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-base">Sin cuadrillas</h3>
                                        <p class="text-xs text-gray-550 dark:text-gray-400 text-center leading-relaxed">No se encontraron cuadrillas que coincidan con la búsqueda o los filtros aplicados.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforelse

                    {{-- Skeleton rows (ocultos por defecto, visibles durante loading) --}}
                    @for($i = 0; $i < 5; $i++)
                        <tr wire:loading.class.remove="hidden" wire:target="loadData,refreshData,delete" class="hidden animate-pulse">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gray-200 dark:bg-white/10 shrink-0"></div>
                                    <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-full w-24"></div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-full w-20"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-full w-6 mx-auto"></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-full w-28"></div>
                            </td>
                            @if($this->rolUsuario === 'CO')
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="h-8 bg-gray-200 dark:bg-white/10 rounded-xl w-16 ml-auto"></div>
                            </td>
                            @endif
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        {{-- Paginación premium --}}
        @if($cuadrillas->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5 bg-gray-50/30 dark:bg-white/2">
                {{ $cuadrillas->links() }}
            </div>
        @endif
    </div>

    <!-- Modals -->
    @livewire('cuadrillas.cuadrilla-form-modal')

    <x-confirm-modal 
        name="confirm-delete-cuadrilla" 
        title="Eliminar Cuadrilla"
        message="¿Estás seguro de que deseas eliminar esta cuadrilla? Si tiene maniobras en proceso no podrá ser eliminada."
        confirmText="Eliminar"
        confirmColor="danger"
        confirmAction="delete"
        :auto-close="false"
        :error="$deleteError"
    />
</div>
