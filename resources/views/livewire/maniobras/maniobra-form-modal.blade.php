<x-modal name="maniobra-modal" title="{{ $maniobraId ? 'Editar Maniobra' : 'Nueva Maniobra' }}" maxWidth="md">
    <form wire:submit.prevent="save">
        
        @if($errors->has('form'))
            <div class="mb-4 p-3 bg-red-50 border border-red-200 text-red-600 rounded-xl text-sm font-medium">
                {{ $errors->first('form') }}
            </div>
        @endif

        <div class="space-y-4">
            <div>
                <x-label for="nombre" value="Nombre *" />
                <x-input id="nombre" type="text" wire:model="nombre" placeholder="Ej. Carga 25kg" maxlength="100" />
                @error('nombre') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            <div>
                <x-label for="descripcion" value="Descripción (Opcional)" />
                <textarea id="descripcion" wire:model="descripcion" rows="3" maxlength="100"
                    class="w-full border-gray-200 dark:border-white/10 bg-white dark:bg-[#1E293B] rounded-2xl p-4 text-sm text-gray-900 dark:text-white focus:border-green-500 focus:ring-0 shadow-sm transition-all duration-200 placeholder:text-gray-400 dark:placeholder:text-gray-500"
                    placeholder="Breve descripción de la maniobra"></textarea>
                @error('descripcion') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>

            @if($maniobraId)
            <div>
                <x-label for="estatus" value="Estatus *" />
                <x-select id="estatus" wire:model="estatus">
                    <option value="A">Activa</option>
                    <option value="I">Inactiva</option>
                </x-select>
                @error('estatus') <span class="text-xs text-red-500 mt-1 block font-medium">{{ $message }}</span> @enderror
            </div>
            @endif
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
