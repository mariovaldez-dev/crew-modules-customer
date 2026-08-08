<div class="space-y-6" wire:init="loadData">
    <!-- Header Page -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Catálogo de maniobras</h1>
            <p class="text-lg text-gray-400 dark:text-gray-500 font-bold tracking-widest mt-1">
                Administra los tipos de maniobra autorizados en el sistema
            </p>
        </div>
        
        <div>
            <button wire:click="$dispatch('open-maniobra-modal')" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-green-600 hover:bg-green-700 text-white text-sm font-bold shadow-md shadow-green-900/10 hover:shadow-lg transition-all duration-200">
                <i class="fa-solid fa-plus text-xs"></i>
                Nueva maniobra
            </button>
        </div>
    </div>

    <!-- Actions bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="relative w-full sm:w-96">
            <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                <i class="fa-solid fa-magnifying-glass text-sm"></i>
            </span>
            <input wire:model.live.debounce.300ms="search" type="search" 
                class="w-full h-12 pl-11 pr-4 bg-white dark:bg-[#1E293B] border border-gray-250 dark:border-white/10 rounded-2xl text-sm text-gray-900 dark:text-white focus:border-green-500 dark:focus:border-green-500 focus:ring-0 shadow-sm transition-colors placeholder:text-gray-400 dark:placeholder:text-gray-500" 
                placeholder="Buscar por nombre...">
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-green-600 text-white text-[11px] font-bold uppercase tracking-wider">
                        <th class="px-6 py-4">Tipo de Movimiento</th>
                        <th class="px-6 py-4">Descripción</th>
                        <th class="px-6 py-4 text-center">Estatus</th>
                        <th class="px-6 py-4 text-right">Acción</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    {{-- Filas de datos (ocultas durante loading) --}}
                    @forelse($maniobras as $maniobra)
                        <tr wire:loading.class.add="hidden" wire:target="loadData,updatedSearch,updatedFilters,resetFilters,resetCustomFilters" class="hover:bg-gray-50/40 dark:hover:bg-white/5 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-green-500/10 to-emerald-500/10 dark:from-emerald-500/20 dark:to-teal-500/10 border border-green-500/20 text-green-700 dark:text-green-400 shadow-sm shrink-0 flex items-center justify-center font-bold text-xs">
                                        <i class="fa-solid fa-dolly text-xs"></i>
                                    </div>
                                    <div>
                                        {{ $maniobra->nombre }}
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400 font-medium">
                                {{ $maniobra->descripcion ?: '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <x-status-badge :status="$maniobra->estatus" />
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="relative group inline-block">
                                    <button wire:click="$dispatch('open-maniobra-modal', { maniobra: {{ json_encode($maniobra->toArray()) }} })"
                                            class="text-green-600 dark:text-green-400 transition-all duration-200 p-2.5 rounded-xl hover:bg-green-50 dark:hover:bg-green-950/30">
                                        <i class="fa-solid fa-pen-to-square text-base"></i>
                                    </button>
                                    <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 hidden group-hover:block bg-gray-900 dark:bg-gray-800 text-white text-[10px] py-1 px-2 rounded-lg font-bold whitespace-nowrap shadow-md z-10 pointer-events-none">
                                        Editar
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @if($readyToLoad)
                            <tr wire:loading.class.add="hidden" wire:target="loadData,updatedSearch,updatedFilters,resetFilters,resetCustomFilters">
                                <td colspan="4" class="px-6 py-16 text-center text-gray-400 dark:text-gray-500">
                                    <div class="flex flex-col items-center gap-3 max-w-sm mx-auto p-6 bg-gray-50/50 dark:bg-white/5 rounded-3xl border border-gray-100/50 dark:border-white/5 shadow-sm">
                                        <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-white/5 flex items-center justify-center text-gray-400">
                                            <i class="fa-regular fa-folder-open text-xl"></i>
                                        </div>
                                        <h3 class="font-bold text-gray-900 dark:text-white text-base">Sin maniobras</h3>
                                        <p class="text-xs text-gray-550 dark:text-gray-400 text-center leading-relaxed">No se encontraron tipos de maniobra registrados que coincidan con la búsqueda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @endforelse

                    {{-- Skeleton rows (ocultos por defecto, visibles durante loading) --}}
                    @for($i = 0; $i < 5; $i++)
                        <tr wire:loading.class.remove="hidden" wire:target="loadData,updatedSearch,updatedFilters,resetFilters,resetCustomFilters" class="hidden animate-pulse">
                            <td class="px-6 py-5 whitespace-nowrap">
                                <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-full w-2/3"></div>
                            </td>
                            <td class="px-6 py-5">
                                <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-full w-4/5"></div>
                            </td>
                            <td class="px-6 py-5 text-center">
                                <div class="h-6 bg-gray-200 dark:bg-white/10 rounded-full w-16 mx-auto"></div>
                            </td>
                            <td class="px-6 py-5 text-right">
                                <div class="h-8 bg-gray-200 dark:bg-white/10 rounded-xl w-10 ml-auto"></div>
                            </td>
                        </tr>
                    @endfor
                </tbody>
            </table>
        </div>

        {{-- Paginación premium --}}
        @if($maniobras->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5 bg-gray-50/30 dark:bg-white/2">
                {{ $maniobras->links() }}
            </div>
        @endif
    </div>

    <!-- Modals -->
    @livewire('maniobras.maniobra-form-modal')
</div>
