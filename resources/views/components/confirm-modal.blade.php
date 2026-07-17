@props([
    'name',
    'title',
    'message',
    'confirmText' => 'Confirmar',
    'confirmColor' => 'danger',
    'confirmAction' => 'confirm',
    'autoClose' => true,
    'error' => null
])

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
    <div class="flex flex-col gap-4">
        <div class="flex items-start gap-4">
            <div class="shrink-0 w-10 h-10 rounded-full flex items-center justify-center {{ $colorClass }}">
                <i class="fa-solid {{ $icon }} text-lg"></i>
            </div>
            <div>
                <p class="text-sm text-gray-600 mt-1">{{ $message }}</p>
            </div>
        </div>

        @if($error)
            <div class="p-3.5 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/40 text-red-600 dark:text-red-400 rounded-2xl text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation text-sm"></i>
                <span>{{ $error }}</span>
            </div>
        @endif
    </div>
    
    <x-slot name="footer">
        <x-button variant="secondary" x-on:click="!loading ? show = false : null" x-bind:disabled="loading">
            Cancelar
        </x-button>
        <!-- Si autoClose es falso, el cierre lo controla el componente Livewire despachando close-modal -->
        <x-button :variant="$confirmColor" wire:click="{{ $confirmAction }}" x-on:click="{{ $autoClose ? 'show = false' : '' }}" x-bind:disabled="loading">
            <span wire:loading.remove wire:target="{{ $confirmAction }}">{{ $confirmText }}</span>
            <span wire:loading wire:target="{{ $confirmAction }}">Procesando...</span>
        </x-button>
    </x-slot>
</x-modal>
