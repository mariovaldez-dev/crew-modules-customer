<div 
    x-data="{ open: false }"
    @open-confirmar-general.window="open = true"
    @close-confirmar-general.window="open = false"
>
    <div x-show="open" style="display: none;" class="relative z-50" aria-labelledby="modal-confirmar-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div 
            x-show="open"
            class="fixed inset-0 bg-gray-900/40 dark:bg-[#0B1115]/80"
            @click="open = false"
        ></div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
                <div 
                    x-show="open"
                    class="relative overflow-hidden rounded-3xl bg-white dark:bg-[#131B20] text-left shadow-xl sm:my-8 w-full sm:max-w-md border border-gray-100 dark:border-white/5"
                >
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5 flex justify-between items-center">
                        <h3 id="modal-confirmar-title" class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                            <i class="fa-solid fa-circle-check text-green-600 dark:text-green-400"></i>
                            Confirmar Corte de Liquidación
                        </h3>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="px-6 py-6">
                        <div class="flex items-start gap-4">
                            <div class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center text-green-600 bg-green-100 dark:bg-green-950/40">
                                <i class="fa-solid fa-circle-question text-lg"></i>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">
                                ¿Estás seguro que deseas confirmar el corte general? Una vez confirmado, se asignarán folios formales y <strong>no podrás regenerarlo ni modificar las maniobras incluidas.</strong>
                            </p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5 flex justify-end gap-3 rounded-b-3xl">
                        <x-button @click="open = false" type="button" variant="secondary">
                            Cancelar
                        </x-button>
                        <x-button @click="$wire.confirmarCorteGeneral(); open = false;" type="button" variant="primary">
                            <span wire:loading.remove wire:target="confirmarCorteGeneral">
                                <i class="fa-solid fa-check-double mr-1.5"></i>
                                Confirmar Corte
                            </span>
                            <span wire:loading wire:target="confirmarCorteGeneral">
                                <i class="fa-solid fa-spinner fa-spin mr-1.5"></i>
                                Procesando...
                            </span>
                        </x-button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
