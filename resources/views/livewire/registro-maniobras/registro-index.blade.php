<div class="space-y-6">
    <!-- Header Page -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Bitácora de Maniobras</h1>
            <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-1">
                Consulta de operaciones registradas, carga por dispositivo y asignación manual
            </p>
        </div>
        
        <div class="flex gap-2 w-full sm:w-auto justify-end">
            <x-button wire:click="export" variant="secondary">
                <i class="fa-solid fa-file-excel text-green-600 dark:text-green-400 mr-2"></i>
                Exportar
            </x-button>
            
            @if($this->rolUsuario === 'CO')
                <x-button wire:click="$dispatch('open-nueva-maniobra-modal')" variant="primary">
                    <i class="fa-solid fa-plus mr-2"></i>
                    Alta Manual
                </x-button>
            @endif
        </div>
    </div>

    <!-- Filters & Actions -->
    <div class="bg-white dark:bg-[#131B20] p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5">
        <div class="flex flex-wrap gap-4 items-end">
            <!-- Search -->
            <div class="w-full md:w-64 relative">
                <x-label value="Búsqueda" />
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-gray-400 dark:text-gray-500">
                        <i class="fa-solid fa-magnifying-glass text-sm"></i>
                    </span>
                    <input wire:model.live.debounce.300ms="search" type="search" 
                        class="w-full h-12 pl-11 pr-4 bg-white dark:bg-[#131B20] border border-gray-200 dark:border-white/10 rounded-2xl text-sm text-gray-900 dark:text-white focus:border-green-500 dark:focus:border-green-500 focus:ring-0 shadow-sm transition-colors placeholder:text-gray-400 dark:placeholder:text-gray-500" 
                        placeholder="Folio, Cuadrilla...">
                </div>
            </div>

            <!-- Rango Fechas -->
            <div class="w-full md:w-40">
                <x-label value="Fecha Inicio" />
                <x-date-input wire:model.live="filters.fechaInicio" />
            </div>
            <div class="w-full md:w-40">
                <x-label value="Fecha Fin" />
                <x-date-input wire:model.live="filters.fechaFin" />
            </div>

            <!-- Almacén -->
            <div class="w-full md:w-56">
                <x-label value="Almacén" />
                <x-select wire:model.live="filters.almacenId">
                    <option value="">Todos los Almacenes</option>
                    @foreach($almacenes as $id => $nombre)
                        <option value="{{ $id }}">{{ $nombre }}</option>
                    @endforeach
                </x-select>
            </div>

            <!-- Estado -->
            <div class="w-full md:w-44">
                <x-label value="Estado" />
                <x-select wire:model.live="filters.estado">
                    <option value="">Todos</option>
                    <option value="En proceso">En proceso</option>
                    <option value="Liquidada">Liquidada</option>
                </x-select>
            </div>

            <!-- Botón Limpiar -->
            <div class="w-full md:w-auto flex items-end">
                <button wire:click="resetCustomFilters" class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 text-gray-700 dark:text-white shadow-sm transition-all duration-200" title="Limpiar Filtros">
                    <i class="fa-solid fa-eraser text-sm"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-gray-400 dark:text-gray-500 text-[10px] font-black uppercase tracking-widest">
                        <th class="px-6 py-4">Folio / Fecha</th>
                        <th class="px-6 py-4">Origen</th>
                        <th class="px-6 py-4">Almacén / Cuadrilla</th>
                        <th class="px-6 py-4">Maniobra / Toneladas</th>
                        <th class="px-6 py-4 text-center">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    @forelse($maniobras as $maniobra)
                        <tr class="hover:bg-gray-50/40 dark:hover:bg-white/5 transition-colors duration-150">
                            <!-- Folio / Fecha -->
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold text-gray-900 dark:text-white">{{ $maniobra->folio }}</span>
                                    @if($maniobra->documentoSap)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg bg-gray-100 dark:bg-white/10 text-[9px] font-extrabold text-gray-650 dark:text-gray-400 uppercase tracking-wider">
                                            SAP: {{ $maniobra->documentoSap }}
                                        </span>
                                    @endif
                                </div>
                                <div class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-0.5">
                                    {{ $maniobra->fecha->format('d/m/Y') }}
                                </div>
                            </td>
                            <!-- Origen -->
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                @if($maniobra->origen === 'APP')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black tracking-wider bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 uppercase">APP</span>
                                @else
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black tracking-wider bg-purple-50 dark:bg-purple-950/40 text-purple-700 dark:text-purple-400 uppercase">MANUAL</span>
                                @endif
                            </td>
                            <!-- Almacen / Cuadrilla -->
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                <div class="font-bold text-gray-900 dark:text-white">{{ $maniobra->almacenNombre }}</div>
                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 font-semibold">{{ $maniobra->cuadrillaNombre }}</div>
                            </td>
                            <!-- Maniobra / Toneladas -->
                            <td class="px-6 py-4.5 whitespace-nowrap">
                                <div class="text-gray-700 dark:text-gray-300 font-semibold">{{ $maniobra->tipoManiobraNombre }}</div>
                                <div class="text-xs font-mono font-bold text-gray-800 dark:text-white mt-0.5">{{ number_format($maniobra->toneladas, 3) }} t</div>
                            </td>
                            <!-- Estado (Badge) -->
                            <td class="px-6 py-4.5 whitespace-nowrap text-center">
                                <x-status-badge :status="$maniobra->estado" />
                                @if($maniobra->corteId)
                                    <div class="text-[10px] text-gray-400 dark:text-gray-500 mt-1 font-bold">Corte #{{ $maniobra->corteId }}</div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-gray-400 dark:text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-regular fa-folder-open text-4xl mb-2 text-gray-300 dark:text-gray-700"></i>
                                    <p class="font-bold">No se encontraron registros</p>
                                    <p class="text-xs">Prueba cambiando los filtros de fecha o búsqueda.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modals -->
    @livewire('registro-maniobras.nueva-maniobra-form')
</div>
