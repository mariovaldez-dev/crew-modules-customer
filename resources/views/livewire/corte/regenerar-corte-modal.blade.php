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
        <!-- Backdrop con blur -->
        <div 
            x-show="open"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-950/60 backdrop-blur-sm"
            @click="open = false"
        ></div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4">
                <div 
                    x-show="open"
                    x-transition:enter="transition ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                    x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                    x-transition:leave="transition ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                    x-transition:leave-end="opacity-0 scale-95 translate-y-4"
                    class="relative w-full sm:max-w-md overflow-hidden rounded-3xl bg-white dark:bg-[#131B20] shadow-2xl border border-gray-100 dark:border-white/5"
                >
                    <!-- Banda de color superior -->
                    <div class="h-1.5 w-full bg-gradient-to-r from-amber-400 via-orange-400 to-yellow-400"></div>

                    <!-- Header -->
                    <div class="px-7 pt-7 pb-5">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <!-- Icono grande con animación -->
                                <div class="shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-amber-500/20">
                                    <i class="fa-solid fa-rotate-right text-white text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold tracking-widest uppercase text-amber-600 dark:text-amber-400 mb-0.5">Recalcular Corte</p>
                                    <h3 id="modal-regenerar-title" class="text-xl font-black text-gray-900 dark:text-white leading-tight">
                                        Regenerar Corte
                                    </h3>
                                </div>
                            </div>
                            <button 
                                @click="open = false" 
                                class="shrink-0 ml-2 w-8 h-8 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 hover:bg-gray-100 dark:hover:bg-white/10 transition-all duration-200"
                            >
                                <i class="fa-solid fa-xmark text-base"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="px-7 pb-6 space-y-5">
                        <p class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                            Se recalcularán los importes del corte tomando en cuenta el estado actual de las maniobras y las tarifas vigentes.
                        </p>

                        <!-- Lista de lo que hará la regeneración -->
                        <div class="rounded-2xl bg-amber-50 dark:bg-amber-950/30 border border-amber-100 dark:border-amber-800/30 p-4 space-y-3">
                            <p class="text-xs font-bold text-amber-700 dark:text-amber-400 uppercase tracking-wider">Al regenerar se ejecutará:</p>
                            <ul class="space-y-2">
                                <li class="flex items-center gap-2.5 text-sm text-amber-800 dark:text-amber-300">
                                    <i class="fa-solid fa-arrow-rotate-right text-amber-500 text-base shrink-0"></i>
                                    Se recalcularán los importes con tarifas actualizadas
                                </li>
                                <li class="flex items-center gap-2.5 text-sm text-amber-800 dark:text-amber-300">
                                    <i class="fa-solid fa-arrow-rotate-right text-amber-500 text-base shrink-0"></i>
                                    Se incluirán nuevas maniobras dentro del rango de fechas
                                </li>
                                <li class="flex items-center gap-2.5 text-sm text-amber-800 dark:text-amber-300">
                                    <i class="fa-solid fa-arrow-rotate-right text-amber-500 text-base shrink-0"></i>
                                    Se resetearán las confirmaciones de cuadrillas
                                </li>
                            </ul>
                        </div>

                        <!-- Nota informativa -->
                        <div class="flex items-center gap-2.5 rounded-xl bg-blue-50 dark:bg-blue-950/30 border border-blue-100 dark:border-blue-800/30 px-4 py-3">
                            <i class="fa-solid fa-circle-info text-blue-500 text-sm shrink-0"></i>
                            <p class="text-xs text-blue-700 dark:text-blue-400 font-medium">
                                El corte permanecerá en estado <strong>Borrador</strong> y podrás volver a revisarlo antes de confirmar.
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-7 py-5 bg-gray-50/80 dark:bg-white/[0.03] border-t border-gray-100 dark:border-white/5 flex justify-end gap-3">
                        <button 
                            @click="open = false" 
                            type="button"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold text-gray-700 dark:text-gray-300 bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 transition-all duration-200"
                        >
                            Cancelar
                        </button>
                        <button 
                            @click="$wire.regenerar(); open = false;" 
                            type="button"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 shadow-lg shadow-amber-500/25 hover:shadow-amber-500/40 transition-all duration-200 active:scale-[0.98]"
                        >
                            <span wire:loading.remove wire:target="regenerar" class="flex items-center gap-2">
                                <i class="fa-solid fa-rotate-right text-base"></i>
                                Sí, Regenerar
                            </span>
                            <span wire:loading wire:target="regenerar" class="flex items-center gap-2">
                                <i class="fa-solid fa-spinner fa-spin text-base"></i>
                                Regenerando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
