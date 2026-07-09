@props(['etapas'])

<div class="flex gap-px overflow-hidden rounded-sm h-1 mb-3 w-full bg-gray-100">
    @foreach($etapas as $index => $etapa)
    @php
    /* Calcular la duración en días. Se usa max(1) para asegurar que siempre haya al menos una línea visible si es
    el mismo día */
    $duration = max(1, $etapa->diaFin - $etapa->diaInicio);
    $color = \App\UI\Support\ColorHelper::getStageColor($index);
    @endphp
    <div class="h-full first:rounded-l-sm last:rounded-r-sm hover:brightness-110 transition-all cursor-help"
        title="{{ $etapa->nombre }} ({{ $duration }} días)"
        style="flex: {{ $duration }}; background-color: {{ $color }};"></div>
    @endforeach
</div>