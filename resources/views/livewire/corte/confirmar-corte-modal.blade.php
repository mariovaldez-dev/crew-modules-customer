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
                    <div class="h-1.5 w-full bg-gradient-to-r from-emerald-400 via-green-500 to-teal-400"></div>

                    <!-- Header -->
                    <div class="px-7 pt-7 pb-5">
                        <div class="flex items-start justify-between">
                            <div class="flex items-center gap-4">
                                <!-- Icono grande animado -->
                                <div class="shrink-0 w-14 h-14 rounded-2xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center shadow-lg shadow-green-500/20">
                                    <i class="fa-solid fa-check-double text-white text-2xl"></i>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold tracking-widest uppercase text-emerald-600 dark:text-emerald-400 mb-0.5">Acción Irreversible</p>
                                    <h3 id="modal-confirmar-title" class="text-xl font-black text-gray-900 dark:text-white leading-tight">
                                        Confirmar Corte General
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
                            Estás a punto de confirmar el corte de liquidación general. Esta acción dará por cerrado el periodo y no podrá deshacerse.
                        </p>

                        <!-- Checklist de consecuencias -->
                        <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-950/30 border border-emerald-100 dark:border-emerald-800/30 p-4 space-y-3">
                            <p class="text-xs font-bold text-emerald-700 dark:text-emerald-400 uppercase tracking-wider">Al confirmar se ejecutará:</p>
                            <ul class="space-y-2">
                                <li class="flex items-center gap-2.5 text-sm text-emerald-800 dark:text-emerald-300">
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-base shrink-0"></i>
                                    Se asignarán folios formales a todas las maniobras
                                </li>
                                <li class="flex items-center gap-2.5 text-sm text-emerald-800 dark:text-emerald-300">
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-base shrink-0"></i>
                                    Las maniobras pasarán a estado <strong>Liquidada</strong>
                                </li>
                                <li class="flex items-center gap-2.5 text-sm text-emerald-800 dark:text-emerald-300">
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-base shrink-0"></i>
                                    Quedará disponible para su descarga en PDF
                                </li>
                            </ul>
                        </div>

                        <!-- Advertencia roja -->
                        <div class="flex items-center gap-2.5 rounded-xl bg-red-50 dark:bg-red-950/30 border border-red-100 dark:border-red-800/30 px-4 py-3">
                            <i class="fa-solid fa-triangle-exclamation text-red-500 text-sm shrink-0"></i>
                            <p class="text-xs text-red-700 dark:text-red-400 font-medium">
                                Una vez confirmado <strong>no podrás regenerarlo</strong> ni modificar las maniobras incluidas.
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
                            @click="$wire.confirmarCorteGeneral(); open = false;" 
                            type="button"
                            class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl text-sm font-bold text-white bg-gradient-to-r from-emerald-500 to-green-600 hover:from-emerald-600 hover:to-green-700 shadow-lg shadow-green-500/25 hover:shadow-green-500/40 transition-all duration-200 active:scale-[0.98]"
                        >
                            <span wire:loading.remove wire:target="confirmarCorteGeneral" class="flex items-center gap-2">
                                <i class="fa-solid fa-check-double text-base"></i>
                                Confirmar Corte
                            </span>
                            <span wire:loading wire:target="confirmarCorteGeneral" class="flex items-center gap-2">
                                <i class="fa-solid fa-spinner fa-spin text-base"></i>
                                Procesando...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
