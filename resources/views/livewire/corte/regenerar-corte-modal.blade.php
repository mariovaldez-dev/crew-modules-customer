<div 
    x-data="{ open: false }"
    @open-confirmar-regenerar.window="open = true"
    @close-confirmar-regenerar.window="open = false"
>
    <div 
        x-show="open" 
        style="display: none;" 
        class="relative z-50" 
        aria-labelledby="modal-regenerar-title" 
        role="dialog" 
        aria-modal="true"
    >
        <!-- Backdrop -->
        <div 
            x-show="open"
            class="fixed inset-0 bg-gray-900/75"
            @click="open = false"
        ></div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div 
                    x-show="open"
                    class="relative w-full sm:max-w-sm overflow-hidden rounded-2xl bg-white dark:bg-[#131B20] shadow-2xl border border-gray-100 dark:border-white/5"
                >
                    <!-- Header con color ámbar -->
                    <div class="bg-amber-50 dark:bg-amber-950/30 px-6 py-4 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-amber-500 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-rotate-right text-white text-sm"></i>
                            </div>
                            <div>
                                <h3 id="modal-regenerar-title" class="text-base font-bold text-gray-900 dark:text-white">Regenerar Corte</h3>
                                <p class="text-xs text-amber-600 dark:text-amber-400 font-medium">Se recalcularán los importes</p>
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
                            Se recalcularán los importes con las tarifas vigentes y se incluirán nuevas maniobras dentro del rango de fechas del corte.
                        </p>

                        <!-- Nota informativa -->
                        <div class="flex gap-3 p-3 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-900/40">
                            <i class="fa-solid fa-circle-info text-blue-500 mt-0.5 text-sm shrink-0"></i>
                            <p class="text-xs text-blue-700 dark:text-blue-400 leading-relaxed">
                                El corte permanecerá en <strong class="text-blue-800 dark:text-blue-300">Borrador</strong> y se resetearán las confirmaciones de cuadrillas.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-[#0d1519] border-t border-gray-100 dark:border-white/10 flex gap-3 justify-end">
                        <button 
                            @click="open = false" 
                            type="button"
                            class="px-4 py-2 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 transition-all"
                        >
                            Cancelar
                        </button>
                        <button 
                            @click="$wire.regenerar(); open = false;" 
                            type="button"
                            class="px-5 py-2 rounded-xl text-sm font-bold text-white bg-amber-500 hover:bg-amber-600 shadow-sm hover:shadow-amber-500/30 hover:shadow-md transition-all active:scale-[0.98]"
                        >
                            <span wire:loading.remove wire:target="regenerar">
                                <i class="fa-solid fa-rotate-right mr-1.5"></i> Sí, Regenerar
                            </span>
                            <span wire:loading wire:target="regenerar">
                                <i class="fa-solid fa-spinner fa-spin mr-1.5"></i> Regenerando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
