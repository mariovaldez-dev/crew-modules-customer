<div class="space-y-6">
    <!-- Header Page -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Cuentas de Cuadrillas</h1>
            <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-1">
                Administración de equipos de trabajo y asignación de puntos de venta
            </p>
        </div>
        
        @if($this->rolUsuario === 'CO')
        <div>
            <button wire:click="$dispatch('open-cuadrilla-modal')" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-green-600 hover:bg-green-700 text-white text-sm font-bold shadow-md shadow-green-900/10 hover:shadow-lg transition-all duration-200">
                <i class="fa-solid fa-plus text-xs"></i>
                Nueva Cuadrilla
            </button>
        </div>
        @endif
    </div>

    <!-- Actions and Filters bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
            <!-- Search -->
            <div class="relative w-full sm:w-72">
                <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                    <i class="fa-solid fa-magnifying-glass text-sm"></i>
                </span>
                <input wire:model.live.debounce.300ms="search" type="search" 
                    class="w-full h-12 pl-11 pr-4 bg-white dark:bg-[#131B20] border border-gray-200 dark:border-white/10 rounded-2xl text-sm text-gray-900 dark:text-white focus:border-green-500 dark:focus:border-green-500 focus:ring-0 shadow-sm transition-colors placeholder:text-gray-400 dark:placeholder:text-gray-500" 
                    placeholder="Buscar cuadrilla...">
            </div>

            <!-- Filter PV -->
            <div class="w-full sm:w-64">
                <x-select wire:model.live="filters.puntoVentaId">
                    <option value="">Todos los PV</option>
                    @foreach($puntosVenta as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </x-select>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-gray-400 dark:text-gray-500 text-[10px] font-black uppercase tracking-widest">
                        <th class="px-6 py-4">Cuadrilla</th>
                        <th class="px-6 py-4">Líder</th>
                        <th class="px-6 py-4 text-center">Miembros</th>
                        <th class="px-6 py-4">Punto de Venta</th>
                        @if($this->rolUsuario === 'CO')
                            <th class="px-6 py-4 text-right">Acciones</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    @forelse($cuadrillas as $cuadrilla)
                        <tr class="hover:bg-gray-50/40 dark:hover:bg-white/5 transition-colors duration-150">
                            <!-- Cuadrilla (avatar style) -->
                            <td class="px-6 py-4 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-50 dark:bg-green-950/40 border border-green-100/50 dark:border-green-800/30 flex items-center justify-center font-bold text-xs text-green-700 dark:text-green-400 shrink-0">
                                    {{ substr(trim($cuadrilla->nombre), 0, 1) }}
                                </div>
                                <div class="font-bold text-gray-900 dark:text-white">
                                    {{ $cuadrilla->nombre }}
                                </div>
                            </td>
                            <!-- Líder -->
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400 font-medium">
                                {{ $cuadrilla->lider }}
                            </td>
                            <!-- Miembros -->
                            <td class="px-6 py-4 whitespace-nowrap text-center font-mono font-bold text-gray-800 dark:text-white">
                                {{ $cuadrilla->miembros }}
                            </td>
                            <!-- Punto de Venta -->
                            <td class="px-6 py-4 whitespace-nowrap text-gray-600 dark:text-gray-400 font-semibold text-xs">
                                {{ $puntosVenta[$cuadrilla->puntoVentaId] ?? 'N/A' }}
                            </td>
                            <!-- Acciones -->
                            @if($this->rolUsuario === 'CO')
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <button wire:click="$dispatch('open-cuadrilla-modal', { cuadrilla: {{ json_encode($cuadrilla->toArray()) }} })" 
                                        class="text-gray-400 hover:text-green-600 dark:text-gray-500 dark:hover:text-green-400 transition-all duration-200 p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5"
                                        title="Editar">
                                    <i class="fa-solid fa-pen-to-square text-base"></i>
                                </button>
                                <button wire:click="confirmDelete({{ $cuadrilla->id }})" 
                                        class="text-gray-400 hover:text-red-600 dark:text-gray-500 dark:hover:text-red-400 transition-all duration-200 p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 ml-1"
                                        title="Eliminar">
                                    <i class="fa-solid fa-trash-can text-base"></i>
                                </button>
                            </td>
                            @endif
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-gray-400 dark:text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-regular fa-folder-open text-4xl mb-2 text-gray-300 dark:text-gray-700"></i>
                                    <p class="font-bold">No se encontraron cuadrillas</p>
                                    <p class="text-xs">Intenta con otro filtro o registra una nueva.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
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
    />
</div>
