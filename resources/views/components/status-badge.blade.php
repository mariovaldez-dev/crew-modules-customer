@props(['status'])

@php
    $normalizado = strtolower(trim($status));

    $classes = match($normalizado) {
        'activo', 'a', 'confirmado' => 'bg-emerald-500/15 text-emerald-800 dark:text-emerald-300 border-emerald-500/30 shadow-sm shadow-emerald-500/10',
        'inactivo', 'i'        => 'bg-rose-500/15 text-rose-800 dark:text-rose-300 border-rose-500/30 shadow-sm shadow-rose-500/10',
        'en proceso', 'sin confirmar', 'borrador' => 'bg-amber-500/15 text-amber-800 dark:text-amber-300 border-amber-500/30 shadow-sm shadow-amber-500/10',
        'liquidado', 'liquidada'=> 'bg-indigo-500/15 text-indigo-800 dark:text-indigo-300 border-indigo-500/30 shadow-sm shadow-indigo-500/10',
        default                => 'bg-gray-500/15 text-gray-800 dark:text-gray-300 border-gray-500/30 shadow-sm'
    };

    $label = match($normalizado) {
        'activo', 'a'          => 'Activo',
        'inactivo', 'i'        => 'Inactivo',
        'confirmado'           => 'Confirmado',
        'sin confirmar', 'borrador' => 'Sin Confirmar',
        default                => $status
    };
@endphp

<span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border transition-colors duration-150 {{ $classes }}">
    @if($normalizado === 'activo' || $normalizado === 'a' || $normalizado === 'confirmado')
        <span class="relative flex h-1.5 w-1.5 mr-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-emerald-500"></span>
        </span>
    @elseif($normalizado === 'inactivo' || $normalizado === 'i')
        <span class="h-1.5 w-1.5 rounded-full bg-rose-500 mr-2"></span>
    @elseif($normalizado === 'en proceso' || $normalizado === 'sin confirmar' || $normalizado === 'borrador')
        <span class="relative flex h-1.5 w-1.5 mr-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-amber-500"></span>
        </span>
    @elseif($normalizado === 'liquidado' || $normalizado === 'liquidada')
        <span class="h-1.5 w-1.5 rounded-full bg-indigo-500 mr-2 shadow-[0_0_8px_rgba(99,102,241,0.6)]"></span>
    @else
        <span class="h-1.5 w-1.5 rounded-full bg-gray-400 mr-2"></span>
    @endif
    {{ $label }}
</span>
