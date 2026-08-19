<div>
<x-modal name="nueva-maniobra-modal" title="Alta Manual de Maniobra" maxWidth="lg">
    <form wire:submit.prevent="save" wire:init="loadData">
        
        @if($errors->has('form'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-medium">
                {{ $errors->first('form') }}
            </div>
        @endif

        <div class="space-y-6">
            <!-- Sección: Datos Principales -->
            <div class="p-5 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-white/5">
                <h4 class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4"><i class="fa-solid fa-location-dot mr-1"></i> Ubicación y Asignación</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <x-label for="fecha" value="Fecha *" />
                        <x-date-input id="fecha" wire:model="fecha" disabled />
                        @error('fecha') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-label for="almacenId" value="Almacén *" />
                        <x-select id="almacenId" wire:model.live="almacenId" :disabled="!$readyToLoad"
                                  :options="['' => $readyToLoad ? 'Seleccione...' : 'Cargando...'] + $almacenes"
                                  placeholder="{{ $readyToLoad ? 'Seleccione...' : 'Cargando...' }}" />
                        @error('almacenId') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-label for="cuadrillaId" value="Cuadrilla *" />
                        <x-select id="cuadrillaId" wire:model="cuadrillaId" :disabled="empty($cuadrillas)"
                                  :options="['' => 'Seleccione...'] + $cuadrillas"
                                  placeholder="Seleccione..." />
                        @error('cuadrillaId') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Sección: Detalles de la Operación -->
            <div class="p-5 bg-gray-50 dark:bg-white/5 rounded-2xl border border-gray-100 dark:border-white/5">
                <h4 class="text-xs font-black text-gray-500 dark:text-gray-400 uppercase tracking-widest mb-4"><i class="fa-solid fa-box-open mr-1"></i> Detalles de la Operación</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="col-span-1 sm:col-span-2">
                        <x-label for="tipoManiobraId" value="Tipo de Maniobra *" />
                        <x-select id="tipoManiobraId" wire:model="tipoManiobraId" :disabled="!$readyToLoad"
                                  :options="['' => $readyToLoad ? 'Seleccione...' : 'Cargando...'] + $tiposManiobra"
                                  placeholder="{{ $readyToLoad ? 'Seleccione...' : 'Cargando...' }}" />
                        @error('tipoManiobraId') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-label for="toneladas" value="Toneladas" />
                        <div class="relative w-full">
                            <input 
                                type="number" 
                                id="toneladas" 
                                wire:model.live.debounce.300ms="toneladas" 
                                step="0.001" 
                                min="0.001"
                                class="w-full h-12 border-gray-200 dark:border-white/10 bg-white dark:bg-[#1E293B] rounded-2xl pr-12 pl-4 text-sm text-gray-900 dark:text-white focus:border-green-500 focus:ring-2 focus:ring-green-500/50 dark:focus:ring-green-500/30 shadow-sm transition-all duration-300 hover:border-gray-300 dark:hover:border-white/20 placeholder:text-gray-400 dark:placeholder:text-gray-500"
                            >
                            <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                                <span class="text-gray-400 dark:text-gray-500 text-[11px] font-black uppercase tracking-wider bg-gray-100 dark:bg-white/10 px-2 py-1 rounded-lg">Ton</span>
                            </div>
                        </div>
                        @error('toneladas') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-label for="documentoSap" value="Documento SAP (Opcional)" />
                        <x-input id="documentoSap" type="text" wire:model="documentoSap" placeholder="Ej. SAP-12345" maxlength="50" />
                        @error('documentoSap') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <x-button variant="secondary" x-on:click="show = false">
                Cancelar
            </x-button>
            <x-button type="button" wire:click="save" variant="primary">
                <span wire:loading.remove wire:target="save">Registrar</span>
                <span wire:loading wire:target="save">Registrando...</span>
            </x-button>
        </x-slot>
    </form>
</x-modal>

<x-confirm-modal 
    name="confirmar-maniobra-cero-modal"
    title="Alerta de Confirmación"
    :message="$mensajeConfirmacionZero"
    confirmText="Sí"
    confirmColor="warning"
    confirmAction="confirmarSaveZero"
    :autoClose="false"
/>
</div>
