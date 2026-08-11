<x-modal name="cuadrilla-modal" title="{{ $cuadrillaId ? 'Editar Cuadrilla' : 'Nueva Cuadrilla' }}" maxWidth="2xl">
    <form wire:submit.prevent="save">
        <div wire:init="loadData">

            @if($errors->has('form'))
                <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-medium">
                    {{ $errors->first('form') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Datos Generales -->
                <div class="space-y-4 col-span-1 sm:col-span-2">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white pb-2">Datos Generales</h4>

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
                            @if($pvLoaded)
                                <x-select id="puntoVentaId" wire:model="puntoVentaId" 
                                          :options="['' => 'Seleccione un PV'] + $puntosVenta"
                                          placeholder="Seleccione un PV" />
                            @else
                                <div class="h-12 w-full rounded-2xl bg-gray-200 dark:bg-white/10 animate-pulse"></div>
                            @endif
                            @error('puntoVentaId') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
                        </div>
                    </div>
                </div>

                <!-- Tarifas por Maniobra (dinámicas) -->
                <div class="space-y-4 col-span-1 sm:col-span-2 mt-4">
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white pb-2">
                        Tarifas por Maniobra
                        <span class="ml-2 text-[10px] font-bold text-gray-500 uppercase tracking-widest">(Opcional)</span>
                    </h4>

                    @if($maniobrasLoaded)
                        @if(count($maniobras) > 0)
                            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                                @foreach($maniobras as $idManiobra => $nombreManiobra)
                                    <div>
                                        <x-label :for="'tarifa_'.$idManiobra" :value="$nombreManiobra" />
                                        <x-currency-input
                                            :id="'tarifa_'.$idManiobra"
                                            wire:model="tarifas.{{ $idManiobra }}"
                                        />
                                        @error("tarifas.$idManiobra")
                                            <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span>
                                        @enderror
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex items-center gap-2 text-sm text-gray-400 dark:text-gray-500 py-4">
                                <i class="fa-solid fa-triangle-exclamation text-amber-400"></i>
                                No hay tipos de maniobra activos registrados. Crea uno primero en el catálogo.
                            </div>
                        @endif
                    @else
                        {{-- Skeleton mientras cargan las maniobras --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            @for($i = 0; $i < 6; $i++)
                                <div class="space-y-2">
                                    <div class="h-4 w-24 rounded-lg bg-gray-200 dark:bg-white/10 animate-pulse"></div>
                                    <div class="h-12 w-full rounded-2xl bg-gray-200 dark:bg-white/10 animate-pulse"></div>
                                </div>
                            @endfor
                        </div>
                    @endif
                </div>
            </div>

        </div>{{-- /wire:init --}}

        <x-slot name="footer">
            <x-button variant="secondary" x-on:click="!loading ? show = false : null" x-bind:disabled="loading">
                Cancelar
            </x-button>
            <x-button type="button" wire:click="save" variant="primary" x-bind:disabled="loading">
                <span wire:loading.remove wire:target="save">Guardar</span>
                <span wire:loading wire:target="save">Guardando...</span>
            </x-button>
        </x-slot>
    </form>
</x-modal>
