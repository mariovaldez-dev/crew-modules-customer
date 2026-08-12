<div 
    x-data="{ open: false }"
    @open-confirmar-general.window="open = true"
    @close-confirmar-general.window="open = false"
>
    <div 
        x-show="open" 
        style="display: none;" 
        class="relative z-50" 
        aria-labelledby="modal-confirmar-title" 
        role="dialog" 
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-950/50 backdrop-blur-sm"
            @click="open = false"
        ></div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div 
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full sm:max-w-sm overflow-hidden rounded-2xl bg-white dark:bg-[#131B20] shadow-2xl border border-gray-100 dark:border-white/5"
                >
                    <!-- Header con color de alerta positiva -->
                    <div class="bg-emerald-50 dark:bg-emerald-950/40 border-b border-emerald-100 dark:border-emerald-900/40 px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-emerald-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-check text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 id="modal-confirmar-title" class="text-base font-bold text-gray-900 dark:text-white">Confirmar Corte</h3>
                                <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Acción irreversible</p>
                            </div>
                        </div>
                        <button 
                            @click="open = false" 
                            class="w-7 h-7 rounded-lg flex items-center justify-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 hover:bg-black/5 dark:hover:bg-white/10 transition-all"
                        >
                            <i class="fa-solid fa-xmark text-sm"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-5 space-y-4">
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                            Al confirmar el corte general se asignarán folios formales, las maniobras quedarán como <strong class="text-gray-800 dark:text-gray-200">Liquidadas</strong> y el PDF estará disponible para descarga.
                        </p>

                        <!-- Advertencia -->
                        <div class="flex gap-3 p-3 rounded-xl bg-red-50 dark:bg-red-950/30 border border-red-100 dark:border-red-900/40">
                            <i class="fa-solid fa-triangle-exclamation text-red-500 mt-0.5 text-sm shrink-0"></i>
                            <p class="text-xs text-red-700 dark:text-red-400 leading-relaxed">
                                Una vez confirmado <strong>no podrás regenerar el corte</strong> ni modificar las maniobras incluidas.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-white/[0.03] border-t border-gray-100 dark:border-white/5 flex gap-3 justify-end">
                        <button 
                            @click="open = false" 
                            type="button"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 transition-all"
                        >
                            Cancelar
                        </button>
                        <button 
                            @click="$wire.confirmarCorteGeneral(); open = false;" 
                            type="button"
                            class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-emerald-500 hover:bg-emerald-600 shadow-sm hover:shadow-emerald-500/30 hover:shadow-md transition-all active:scale-[0.98]"
                        >
                            <span wire:loading.remove wire:target="confirmarCorteGeneral">
                                <i class="fa-solid fa-check mr-1.5"></i> Confirmar Corte
                            </span>
                            <span wire:loading wire:target="confirmarCorteGeneral">
                                <i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
