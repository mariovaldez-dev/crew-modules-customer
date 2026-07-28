<div 
    x-data="{ 
        open: false, 
        cuadrillaId: null, 
        nombre: '', 
        estaConfirmada: 0,
        maniobras: [] 
    }"
    @open-resumen-modal.window="
        cuadrillaId = $event.detail.cuadrillaId;
        nombre = $event.detail.nombre;
        estaConfirmada = $event.detail.estaConfirmada;
        
        // El SP podría retornar el JSON como string en algunos casos, por si acaso lo parseamos
        let m = $event.detail.maniobras;
        if(typeof m === 'string') {
            try { m = JSON.parse(m); } catch(e) { m = []; }
        }
        maniobras = m;
        
        open = true;
    "
    @close-resumen-modal.window="open = false"
>
    <div x-show="open" style="display: none;" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <!-- Backdrop -->
        <div 
            x-show="open"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-gray-900/40 dark:bg-[#0B1115]/80 transition-opacity"
            @click="open = false"
        ></div>

        <!-- Modal Panel -->
        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div 
                    x-show="open"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                    x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                    class="relative transform overflow-hidden rounded-3xl bg-white dark:bg-[#131B20] text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-4xl border border-gray-100 dark:border-white/5"
                >
                    <!-- Header -->
                    <div class="px-6 py-5 border-b border-gray-100 dark:border-white/5 bg-gray-50/50 dark:bg-white/5 flex justify-between items-center">
                        <div>
                            <h3 class="text-lg font-black text-gray-900 dark:text-white flex items-center gap-2">
                                <i class="fa-solid fa-list-check text-green-600 dark:text-green-400"></i>
                                Resumen de Maniobras
                            </h3>
                            <p class="text-sm font-bold text-gray-500 mt-1" x-text="'Cuadrilla: ' + nombre"></p>
                        </div>
                        <button @click="open = false" class="text-gray-400 hover:text-gray-500 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>

                    <!-- Content (Tabla Anidada) -->
                    <div class="px-6 py-6 max-h-[60vh] overflow-y-auto">
                        <template x-if="maniobras.length > 0">
                            <div class="border border-gray-100 dark:border-white/5 rounded-2xl overflow-hidden">
                                <table class="w-full text-left border-collapse">
                                    <thead>
                                        <tr class="bg-gray-50 dark:bg-white/5 text-gray-500 dark:text-gray-400 text-[10px] font-bold uppercase tracking-wider">
                                            <th class="px-4 py-3">Fecha</th>
                                            <th class="px-4 py-3">Concepto</th>
                                            <th class="px-4 py-3 text-right">Toneladas</th>
                                            <th class="px-4 py-3 text-right">Tarifa</th>
                                            <th class="px-4 py-3 text-right">Monto</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                                        <template x-for="maniobra in maniobras" :key="maniobra.maniobraId">
                                            <tr class="hover:bg-gray-50/50 dark:hover:bg-white/5">
                                                <td class="px-4 py-3 whitespace-nowrap text-gray-600 dark:text-gray-400" x-text="maniobra.fecha.split('T')[0]"></td>
                                                <td class="px-4 py-3 font-semibold text-gray-900 dark:text-white" x-text="maniobra.concepto"></td>
                                                <td class="px-4 py-3 font-mono text-gray-600 dark:text-gray-300 text-right" x-text="parseFloat(maniobra.toneladas).toFixed(3)"></td>
                                                <td class="px-4 py-3 font-mono text-gray-500 dark:text-gray-400 text-right" x-text="'$' + parseFloat(maniobra.tarifaAplicada).toFixed(2)"></td>
                                                <td class="px-4 py-3 font-mono font-bold text-gray-900 dark:text-white text-right" x-text="'$' + parseFloat(maniobra.montoTotal).toFixed(2)"></td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        <template x-if="maniobras.length === 0">
                            <div class="text-center py-8">
                                <div class="w-16 h-16 bg-gray-50 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4 border border-gray-100 dark:border-white/10">
                                    <i class="fa-solid fa-folder-open text-2xl text-gray-400 dark:text-gray-500"></i>
                                </div>
                                <h3 class="text-base font-bold text-gray-900 dark:text-white mb-1">Sin maniobras</h3>
                                <p class="text-sm text-gray-500 dark:text-gray-400">Esta cuadrilla no realizó maniobras en el rango de fechas de este corte.</p>
                            </div>
                        </template>
                    </div>

                    <!-- Footer -->
                    <div class="px-6 py-4 bg-gray-50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5 flex justify-end gap-3 rounded-b-3xl items-center">
                        <x-button @click="open = false" type="button" variant="secondary">
                            Cerrar
                        </x-button>

                        <template x-if="!estaConfirmada">
                            <!-- El botón manda llamar a la función Livewire en el backend -->
                            <x-button @click="$wire.confirmarManiobrasCuadrilla(); open = false;" type="button" variant="primary">
                                <i class="fa-solid fa-check mr-2"></i>
                                Validar y Confirmar Cuadrilla
                            </x-button>
                        </template>
                        <template x-if="estaConfirmada">
                            <div class="px-4 py-2 bg-green-50 dark:bg-green-950/40 border border-green-100/50 dark:border-green-800/30 rounded-xl flex items-center gap-2">
                                <i class="fa-solid fa-circle-check text-green-600 dark:text-green-400"></i>
                                <span class="text-sm font-bold text-green-700 dark:text-green-400">Cuadrilla Confirmada</span>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
