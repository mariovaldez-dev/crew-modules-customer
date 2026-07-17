<div class="space-y-6" wire:init="loadData">
    <div class="mb-8">
        <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Consulta de Tarifas</h1>
        <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-1">
            Revisión de precios por concepto de maniobras y cuadrilla
        </p>
    </div>

    {{-- Skeleton de carga --}}
    @if(!$readyToLoad)
        <div class="space-y-4">
            @for($i = 0; $i < 2; $i++)
                <div class="animate-pulse bg-white dark:bg-[#131B20] rounded-3xl border border-gray-100 dark:border-white/5 p-6 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div class="h-5 bg-gray-200 dark:bg-white/10 rounded-full w-40"></div>
                        <div class="h-5 bg-gray-200 dark:bg-white/10 rounded-full w-6"></div>
                    </div>
                    <div class="space-y-3">
                        @for($j = 0; $j < 3; $j++)
                            <div class="flex gap-6">
                                <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-full w-32"></div>
                                <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-full w-16"></div>
                                <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-full w-16"></div>
                                <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-full w-16"></div>
                                <div class="h-4 bg-gray-200 dark:bg-white/10 rounded-full w-16"></div>
                            </div>
                        @endfor
                    </div>
                </div>
            @endfor
        </div>
    @elseif($esAdministrador)
        {{-- Vista Administrador: Acordeones por Zona --}}
        @forelse($data as $zona => $cuadrillas)
            <x-accordion-zone :zona="$zona" :cuadrillas="$cuadrillas">
                @include('livewire.tarifas.partials.accordion-tarifas', ['cuadrillas' => $cuadrillas, 'tiposManiobra' => $tiposManiobra])
            </x-accordion-zone>
        @empty
            <div class="bg-white dark:bg-[#131B20] rounded-3xl border border-gray-100 dark:border-white/5 p-12 text-center shadow-sm">
                <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gray-50 dark:bg-white/5 mb-4">
                    <i class="fa-solid fa-map-location-dot text-2xl text-gray-400 dark:text-gray-500"></i>
                </div>
                <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-1">No hay zonas registradas</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400">Aún no existen cuadrillas con tarifas asignadas en el sistema.</p>
            </div>
        @endforelse
    @else
        {{-- Vista Coordinadora: Tabla plana de su Zona --}}
        <div class="bg-white dark:bg-[#131B20] rounded-3xl border border-gray-100 dark:border-white/5 p-6 shadow-sm">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white">{{ $claveZonaCO }} - {{ $nombreZonaCO }}</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Tarifas vigentes autorizadas para las cuadrillas de tu zona.</p>
            </div>

            
            @if(count($data) > 0)
                @include('livewire.tarifas.partials.accordion-tarifas', ['cuadrillas' => $data, 'tiposManiobra' => $tiposManiobra])
            @else
                <div class="text-center py-12 text-gray-400 dark:text-gray-500">
                    <i class="fa-regular fa-folder-open text-3xl mb-2"></i>
                    <p class="font-bold">No hay cuadrillas registradas en tu zona.</p>
                </div>
            @endif
        </div>
    @endif
</div>

