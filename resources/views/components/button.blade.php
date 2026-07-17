@props(['variant' => 'primary', 'type' => 'button'])

@php
$baseClasses = 'inline-flex items-center justify-center px-5 py-3 text-sm font-bold rounded-2xl transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500/50 dark:focus:ring-offset-[#0B0F13] active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed disabled:active:scale-100';

$variantClasses = match($variant) {
    'primary' => 'bg-green-600 text-white hover:bg-green-500 shadow-lg shadow-green-900/20 hover:shadow-green-900/40 dark:shadow-green-900/40 border border-green-500 hover:-translate-y-0.5',
    'secondary' => 'bg-white dark:bg-white/5 text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-white/10 border border-gray-200 dark:border-white/10 shadow-sm hover:shadow-md hover:-translate-y-0.5',
    'danger' => 'bg-red-600 text-white hover:bg-red-500 shadow-lg shadow-red-900/20 hover:shadow-red-900/40 dark:shadow-red-900/40 border border-red-500 hover:-translate-y-0.5',
    default => 'bg-green-600 text-white hover:bg-green-500',
};
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "$baseClasses $variantClasses"]) }}>
    {{ $slot }}
</button>
