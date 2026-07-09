<div class="space-y-6">
    <!-- Header Page -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Catálogo de Maniobras</h1>
            <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-1">
                Administra los tipos de maniobra autorizados en el sistema
            </p>
        </div>
        
        <div>
            <button wire:click="$dispatch('open-maniobra-modal')" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-green-600 hover:bg-green-700 text-white text-sm font-bold shadow-md shadow-green-900/10 hover:shadow-lg transition-all duration-200">
                <i class="fa-solid fa-plus text-xs"></i>
                Nueva Maniobra
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
                class="w-full h-12 pl-11 pr-4 bg-white dark:bg-[#131B20] border border-gray-250 dark:border-white/10 rounded-2xl text-sm text-gray-900 dark:text-white focus:border-green-500 dark:focus:border-green-500 focus:ring-0 shadow-sm transition-colors placeholder:text-gray-400 dark:placeholder:text-gray-500" 
                placeholder="Buscar por nombre...">
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-gray-400 dark:text-gray-500 text-[10px] font-black uppercase tracking-widest">
                        <th class="px-6 py-4">Nombre</th>
                        <th class="px-6 py-4">Descripción</th>
                        <th class="px-6 py-4 text-center">Estatus</th>
                        <th class="px-6 py-4 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    @forelse($maniobras as $maniobra)
                        <tr class="hover:bg-gray-50/40 dark:hover:bg-white/5 transition-colors duration-150">
                            <td class="px-6 py-4.5 whitespace-nowrap font-bold text-gray-900 dark:text-white">
                                {{ $maniobra->nombre }}
                            </td>
                            <td class="px-6 py-4.5 text-gray-600 dark:text-gray-400 font-medium">
                                {{ $maniobra->descripcion ?: '-' }}
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-center">
                                <x-status-badge :status="$maniobra->estatus" />
                            </td>
                            <td class="px-6 py-4.5 whitespace-nowrap text-right">
                                <button wire:click="$dispatch('open-maniobra-modal', { maniobra: {{ json_encode($maniobra->toArray()) }} })" 
                                        class="text-gray-400 hover:text-green-600 dark:text-gray-500 dark:hover:text-green-400 transition-all duration-200 p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5"
                                        title="Editar">
                                    <i class="fa-solid fa-pen-to-square text-base"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center text-gray-400 dark:text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-regular fa-folder-open text-4xl mb-2 text-gray-300 dark:text-gray-700"></i>
                                    <p class="font-bold">No hay maniobras registradas</p>
                                    <p class="text-xs">Intenta con otro término de búsqueda o registra una nueva.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals -->
    @livewire('maniobras.maniobra-form-modal')
</div>
