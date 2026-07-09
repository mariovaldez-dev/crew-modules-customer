@props(['status'])

@php
    $classes = match($status) {
        'A' => 'bg-green-100 text-green-800 border-green-200',
        'I' => 'bg-red-100 text-red-800 border-red-200',
        'En proceso' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
        'Liquidado' => 'bg-blue-100 text-blue-800 border-blue-200',
        default => 'bg-gray-100 text-gray-800 border-gray-200'
    };
    
    $label = match($status) {
        'A' => 'Activa',
        'I' => 'Inactiva',
        default => $status
    };
@endphp

<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $classes }}">
    {{ $label }}
</span>
