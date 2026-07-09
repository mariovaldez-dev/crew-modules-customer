@props(['variant' => 'primary', 'type' => 'button'])

@php
$baseClasses = 'inline-flex items-center justify-center px-5 py-3 text-sm font-bold rounded-2xl transition-all duration-200 focus:outline-none focus:ring-0 disabled:opacity-50 disabled:cursor-not-allowed';

$variantClasses = match($variant) {
    'primary' => 'bg-green-600 text-white hover:bg-green-700 shadow-md shadow-green-900/10 hover:shadow-lg',
    'secondary' => 'bg-white dark:bg-white/5 text-gray-700 dark:text-white hover:bg-gray-50 dark:hover:bg-white/10 border border-gray-200 dark:border-white/10 shadow-sm',
    'danger' => 'bg-red-600 text-white hover:bg-red-700 shadow-md shadow-red-900/10 hover:shadow-lg',
    default => 'bg-green-600 text-white hover:bg-green-700',
};
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "$baseClasses $variantClasses"]) }}>
    {{ $slot }}
</button>
