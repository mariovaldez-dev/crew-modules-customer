<x-modal name="cuadrilla-modal" title="{{ $cuadrillaId ? 'Editar Cuadrilla' : 'Nueva Cuadrilla' }}" maxWidth="2xl">
    <form wire:submit.prevent="save">
        
        @if($errors->has('form'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-medium">
                {{ $errors->first('form') }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <!-- Datos Generales -->
            <div class="space-y-4 col-span-1 sm:col-span-2">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-white/5 pb-2">Datos Generales</h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-label for="nombre" value="Nombre *" />
                        <x-input id="nombre" type="text" wire:model="nombre" placeholder="Ej. Cuadrilla Alfa" maxlength="100" />
                        @error('nombre') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-label for="lider" value="Líder *" />
                        <x-input id="lider" type="text" wire:model="lider" placeholder="Nombre completo" maxlength="100" />
                        @error('lider') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-label for="miembros" value="Total de Miembros *" />
                        <x-input id="miembros" type="number" wire:model="miembros" min="1" />
                        @error('miembros') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <x-label for="puntoVentaId" value="Punto de Venta *" />
                        <x-select id="puntoVentaId" wire:model="puntoVentaId">
                            <option value="">Seleccione un PV</option>
                            @foreach($puntosVenta as $id => $nombrePv)
                                <option value="{{ $id }}">{{ $nombrePv }}</option>
                            @endforeach
                        </x-select>
                        @error('puntoVentaId') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- Tarifas -->
            <div class="space-y-4 col-span-1 sm:col-span-2 mt-4">
                <h4 class="text-sm font-bold text-gray-900 dark:text-white border-b border-gray-100 dark:border-white/5 pb-2">Tarifas (Opcional)</h4>
                
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <x-label for="carga25" value="Carga 25kg" />
                        <x-currency-input id="carga25" wire:model="carga25" />
                        @error('carga25') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-label for="carga50" value="Carga 50kg" />
                        <x-currency-input id="carga50" wire:model="carga50" />
                        @error('carga50') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-label for="descarga25" value="Descarga 25kg" />
                        <x-currency-input id="descarga25" wire:model="descarga25" />
                        @error('descarga25') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-label for="descarga50" value="Descarga 50kg" />
                        <x-currency-input id="descarga50" wire:model="descarga50" />
                        @error('descarga50') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-label for="traslado" value="Traslado Interno" />
                        <x-currency-input id="traslado" wire:model="traslado" />
                        @error('traslado') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <x-label for="apaleo" value="Apaleo" />
                        <x-currency-input id="apaleo" wire:model="apaleo" />
                        @error('apaleo') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>
        </div>

        <x-slot name="footer">
            <x-button variant="secondary" x-on:click="show = false">
                Cancelar
            </x-button>
            <x-button type="button" wire:click="save" variant="primary">
                <span wire:loading.remove wire:target="save">Guardar</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </x-button>
        </x-slot>
    </form>
</x-modal>
