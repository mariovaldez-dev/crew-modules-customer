<x-modal name="nueva-maniobra-modal" title="Alta Manual de Maniobra" maxWidth="lg">
    <form wire:submit.prevent="save">
        
        @if($errors->has('form'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-medium">
                {{ $errors->first('form') }}
            </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            
            <div class="col-span-1 sm:col-span-2">
                <x-label for="fecha" value="Fecha *" />
                <x-date-input id="fecha" wire:model="fecha" />
                @error('fecha') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-label for="almacenId" value="Almacén *" />
                <x-select id="almacenId" wire:model.live="almacenId">
                    <option value="">Seleccione...</option>
                    @foreach($almacenes as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </x-select>
                @error('almacenId') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-label for="cuadrillaId" value="Cuadrilla *" />
                <x-select id="cuadrillaId" wire:model="cuadrillaId" :disabled="empty($cuadrillas)">
                    <option value="">Seleccione...</option>
                    @foreach($cuadrillas as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </x-select>
                @error('cuadrillaId') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-label for="tipoManiobraId" value="Tipo de Maniobra *" />
                <x-select id="tipoManiobraId" wire:model="tipoManiobraId">
                    <option value="">Seleccione...</option>
                    @foreach($tiposManiobra as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </x-select>
                @error('tipoManiobraId') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-label for="toneladas" value="Toneladas *" />
                <div class="relative w-full">
                    <input 
                        type="number" 
                        id="toneladas" 
                        wire:model="toneladas" 
                        step="0.001" 
                        min="0.001"
                        class="w-full h-12 border-gray-200 dark:border-white/10 bg-white dark:bg-[#1E293B] rounded-2xl pr-12 pl-4 text-sm text-gray-900 dark:text-white focus:border-green-500 focus:ring-0 shadow-sm transition-all duration-200 placeholder:text-gray-400 dark:placeholder:text-gray-500"
                    >
                    <div class="absolute inset-y-0 right-0 pr-4 flex items-center pointer-events-none">
                        <span class="text-gray-400 dark:text-gray-500 text-xs font-bold uppercase tracking-wider">Ton</span>
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
