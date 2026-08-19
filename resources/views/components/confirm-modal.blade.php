@props([
    'name',
    'title' => 'Confirmar Acción',
    'message',
    'confirmText' => 'Confirmar',
    'confirmColor' => 'danger',
    'confirmAction' => 'confirm',
    'autoClose' => true,
    'error' => null
])

@php
$styles = match ($confirmColor) {
    'danger' => [
        'card' => 'background-color: #FEF2F2; border: 1px solid #FCA5A5;',
        'iconBox' => 'background-color: #EF4444; color: #FFFFFF;',
        'title' => 'color: #991B1B;',
        'sub' => 'color: #B91C1C;',
        'btn' => 'background-color: #DC2626; color: #FFFFFF; border: 1px solid #B91C1C; box-shadow: 0 10px 15px -3px rgba(220, 38, 38, 0.35);',
        'icon' => 'fa-triangle-exclamation'
    ],
    'warning' => [
        'card' => 'background-color: #FFFBEB; border: 1px solid #FDE68A;',
        'iconBox' => 'background-color: #F59E0B; color: #FFFFFF;',
        'title' => 'color: #78350F;',
        'sub' => 'color: #92400E;',
        'btn' => 'background-color: #D97706; color: #FFFFFF; border: 1px solid #B45309; box-shadow: 0 10px 15px -3px rgba(217, 119, 6, 0.35);',
        'icon' => 'fa-triangle-exclamation'
    ],
    'primary' => [
        'card' => 'background-color: #F0FDF4; border: 1px solid #86EFAC;',
        'iconBox' => 'background-color: #16A34A; color: #FFFFFF;',
        'title' => 'color: #14532D;',
        'sub' => 'color: #166534;',
        'btn' => 'background-color: #16A34A; color: #FFFFFF; border: 1px solid #15803D; box-shadow: 0 10px 15px -3px rgba(22, 163, 74, 0.35);',
        'icon' => 'fa-circle-question'
    ],
    default => [
        'card' => 'background-color: #F9FAFB; border: 1px solid #E5E7EB;',
        'iconBox' => 'background-color: #4B5563; color: #FFFFFF;',
        'title' => 'color: #111827;',
        'sub' => 'color: #4B5563;',
        'btn' => 'background-color: #1F2937; color: #FFFFFF; border: 1px solid #111827; box-shadow: 0 10px 15px -3px rgba(31, 41, 55, 0.35);',
        'icon' => 'fa-circle-info'
    ],
};
@endphp

<x-modal :name="$name" :title="$title" maxWidth="md">
    <div class="py-2">
        <div class="p-5 rounded-2xl flex items-start gap-4 shadow-sm" style="{{ $styles['card'] }}">
            <div class="shrink-0 w-12 h-12 rounded-2xl flex items-center justify-center shadow-sm" style="{{ $styles['iconBox'] }}">
                <i class="fa-solid {{ $styles['icon'] }} text-xl"></i>
            </div>
            <div class="pt-0.5 flex-1">
                <p class="text-base font-bold leading-snug" style="{{ $styles['title'] }}">
                    {{ $message }}
                </p>
                <p class="text-xs mt-1 font-medium" style="{{ $styles['sub'] }}">
                    Confirma si deseas proceder con este registro.
                </p>
            </div>
        </div>

        @if($error)
            <div class="mt-4 p-3.5 bg-red-50 dark:bg-red-950/20 border border-red-200 dark:border-red-900/40 text-red-600 dark:text-red-400 rounded-2xl text-xs font-bold flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-sm"></i>
                <span>{{ $error }}</span>
            </div>
        @endif
    </div>
    
    <x-slot name="footer">
        <x-button variant="secondary" x-on:click="!loading ? show = false : null" x-bind:disabled="loading">
            Cancelar
        </x-button>
        <button 
            type="button" 
            wire:click="{{ $confirmAction }}" 
            x-on:click="{{ $autoClose ? 'show = false' : '' }}" 
            x-bind:disabled="loading"
            class="px-6 py-2.5 text-sm font-black rounded-2xl transition-all duration-200 focus:outline-none active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed hover:-translate-y-0.5"
            style="{{ $styles['btn'] }}"
        >
            <span wire:loading.remove wire:target="{{ $confirmAction }}">{{ $confirmText }}</span>
            <span wire:loading wire:target="{{ $confirmAction }}">Procesando...</span>
        </button>
    </x-slot>
</x-modal>
