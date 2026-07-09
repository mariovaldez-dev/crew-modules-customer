@props(['name', 'title', 'message', 'confirmText' => 'Confirmar', 'confirmColor' => 'danger', 'confirmAction' => 'confirm'])

@php
$colorClass = match ($confirmColor) {
    'danger' => 'text-red-600 bg-red-100',
    'primary' => 'text-green-600 bg-green-100',
    default => 'text-gray-600 bg-gray-100',
};
$icon = match ($confirmColor) {
    'danger' => 'fa-triangle-exclamation',
    'primary' => 'fa-circle-question',
    default => 'fa-circle-info',
};
@endphp

<x-modal :name="$name" :title="$title" maxWidth="md">
    <div class="flex items-start gap-4">
        <div class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center {{ $colorClass }}">
            <i class="fa-solid {{ $icon }} text-lg"></i>
        </div>
        <div>
            <p class="text-sm text-gray-600 mt-1">{{ $message }}</p>
        </div>
    </div>
    
    <x-slot name="footer">
        <x-button variant="secondary" x-on:click="show = false">
            Cancelar
        </x-button>
        <!-- The parent Livewire component should intercept this or use wire:click on the component call -->
        <x-button :variant="$confirmColor" wire:click="{{ $confirmAction }}" x-on:click="show = false">
            {{ $confirmText }}
        </x-button>
    </x-slot>
</x-modal>
