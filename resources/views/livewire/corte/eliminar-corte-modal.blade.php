<div 
    x-data="{ open: false }"
    @open-eliminar-borrador.window="open = true"
    @close-eliminar-borrador.window="open = false"
>
    <div 
        x-show="open" 
        style="display: none;" 
        class="relative z-50" 
        aria-labelledby="modal-eliminar-title" 
        role="dialog" 
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div 
            x-show="open"
            class="fixed inset-0 bg-gray-900/40"
            @click="open = false"
        ></div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div 
                    x-show="open"
                    class="relative w-full sm:max-w-sm overflow-hidden rounded-2xl bg-white dark:bg-[#131B20] shadow-2xl border border-gray-100 dark:border-white/5"
                >
                    <!-- Header con color rojo -->
                    <div class="bg-red-50 dark:bg-red-950/30 px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-red-50 dark:bg-red-950/30 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-trash-can text-red-600 dark:text-red-400 text-sm"></i>
                            </div>
                            <div>
                                <h3 id="modal-eliminar-title" class="text-base font-bold text-gray-900 dark:text-white">Eliminar Borrador</h3>
                                <p class="text-xs text-red-600 dark:text-red-400 font-medium">Se cancelará este corte</p>
                            </div>
                        </div>
                        <button 
                            @click="open = false" 
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 hover:bg-black/5 dark:hover:bg-white/10 transition-all"
                        >
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5 space-y-4">
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                            ¿Estás seguro de eliminar este borrador de corte?
                        </p>

                        <!-- Advertencia -->
                        <div class="flex gap-3 p-3 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-900/40">
                            <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 text-sm shrink-0"></i>
                            <p class="text-xs text-amber-700 dark:text-amber-400 leading-relaxed">
                                Las maniobras asociadas se <strong class="text-amber-800 dark:text-amber-300">liberarán</strong> y quedarán disponibles para un nuevo corte. Las confirmaciones de cuadrillas se eliminarán.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5 flex gap-3 justify-end rounded-b-2xl">
                        <button 
                            @click="open = false" 
                            type="button"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 transition-all"
                        >
                            Cancelar
                        </button>
                        <button 
                            @click="$wire.eliminarBorrador(); open = false;" 
                            type="button"
                            class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-red-600 hover:bg-red-700 shadow-sm hover:shadow-red-600/30 hover:shadow-md transition-all active:scale-[0.98]"
                        >
                            <span wire:loading.remove wire:target="eliminarBorrador">
                                <i class="fa-solid fa-trash-can mr-1.5"></i> Sí, Eliminar
                            </span>
                            <span wire:loading wire:target="eliminarBorrador">
                                <i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Eliminando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
