    <!-- PDF Preview Modal -->
    <div 
        x-data="{ open: false }"
        @open-pdf-modal.window="open = true"
        @close-pdf-modal.window="open = false"
    >
        <div x-show="open" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div 
                x-show="open"
                class="fixed inset-0 bg-gray-900/60 dark:bg-[#0B1115]/80"
                @click="open = false"
            ></div>

            <div class="fixed inset-0 z-10 flex items-center justify-center p-4">
                <div 
                    x-show="open"
                    style="width: 75vw; height: 80vh; max-width: 1100px; min-width: 600px;"
                    class="relative overflow-hidden rounded-3xl bg-white dark:bg-[#131B20] text-left shadow-2xl flex flex-col border border-gray-100 dark:border-white/5"
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
                            <a href="{{ route('corte-liquidacion.pdf', ['id' => $corte['corteId']]) }}" download class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-green-600 hover:bg-green-700 text-white text-xs font-bold shadow-md shadow-green-900/10 hover:shadow-lg transition-all duration-200 cursor-pointer">
                                <i class="fa-solid fa-download text-xs"></i>
                                Descargar
                            </a>
                            <button @click="open = false" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors p-2 bg-gray-100 dark:bg-white/10 rounded-xl">
                                <i class="fa-solid fa-xmark text-lg leading-none"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Iframe (ocupa el resto de la pantalla) -->
                    <div class="flex-1 min-h-0 bg-gray-100 dark:bg-gray-900 w-full p-4">
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
    </div>
