    <!-- PDF Preview Modal -->
    <div 
        x-data="{ open: false }"
        @open-pdf-modal.window="open = true"
        @close-pdf-modal.window="open = false"
    >
        <div x-show="open" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div 
                x-show="open"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-gray-900/60 dark:bg-[#0B1115]/90 transition-opacity"
                @click="open = false"
            ></div>

            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                    <div 
                        x-show="open"
                        x-transition:enter="ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave="ease-in duration-200"
                        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                        class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-[#131B20] text-left shadow-2xl transition-all w-full h-[90vh] sm:my-8 sm:w-11/12 sm:max-w-5xl flex flex-col border border-gray-100 dark:border-white/5"
                    >
                        <!-- Header -->
                        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5 flex justify-between items-center shrink-0">
                            <div>
                                <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                                    <i class="fa-solid fa-file-pdf text-red-500"></i>
                                    Vista Previa del Reporte
                                </h3>
                            </div>
                            <div class="flex items-center gap-3">
                                <a href="{{ route('corte-liquidacion.pdf', ['id' => $corte['corteId']]) }}" download class="inline-flex items-center justify-center font-bold transition-all duration-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-[#131B20] bg-green-600 hover:bg-green-700 text-white shadow-sm !py-2 !px-4 text-xs">
                                    <i class="fa-solid fa-download mr-2"></i> Descargar
                                </a>
                                <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors p-2 bg-gray-100 dark:bg-white/10 rounded-xl">
                                    <i class="fa-solid fa-xmark text-lg leading-none"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Iframe (ocupa el resto de la pantalla) -->
                        <div class="flex-grow bg-gray-100 dark:bg-gray-900 w-full h-full p-2 sm:p-6">
                            <template x-if="open">
                                <iframe 
                                    src="{{ route('corte-liquidacion.pdf', ['id' => $corte['corteId']]) }}#toolbar=1" 
                                    class="w-full h-full rounded-2xl border-none shadow-sm bg-white"
                                ></iframe>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
