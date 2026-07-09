<div class="space-y-6">
    <!-- Header Page -->
    <div class="mb-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">Corte de Liquidación</h1>
            <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-1">
                Gestión y validación de cortes de liquidación semanales por zona
            </p>
        </div>
    </div>

    <!-- Header de Generación -->
    <div class="bg-white dark:bg-[#131B20] p-6 rounded-3xl shadow-sm border border-gray-100 dark:border-white/5 flex flex-col md:flex-row gap-6 items-end justify-between">
        
        <div class="flex flex-wrap gap-4 w-full md:w-auto">
            <div>
                <x-label value="Zona" />
                <div class="w-full sm:w-48 h-12 px-4 flex items-center bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 rounded-2xl text-sm text-gray-500 dark:text-gray-400 font-semibold shadow-sm">
                    {{ $this->zonaUsuario }}
                </div>
            </div>

            <div class="w-full md:w-40">
                <x-label value="Fecha Inicio" />
                <x-date-input wire:model="fechaInicio" :disabled="$this->corte !== null" />
            </div>
            
            <div class="w-full md:w-40">
                <x-label value="Fecha Fin" />
                <x-date-input wire:model="fechaFin" :disabled="$this->corte && $this->corte->estado === 'confirmado'" />
            </div>
        </div>

        <div class="flex gap-2 w-full md:w-auto justify-end">
            @if(!$this->corte)
                <x-button wire:click="generar" variant="primary">
                    Generar Corte
                </x-button>
            @elseif($this->corte->estado === 'borrador')
                <x-button wire:click="regenerar" variant="secondary">
                    <i class="fa-solid fa-rotate-right mr-2"></i>
                    Regenerar
                </x-button>
            @endif
        </div>
    </div>

    <!-- Resultados del Corte -->
    @if($this->corte)
        <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl shadow-sm overflow-hidden">
            
            <!-- Resumen superior del corte -->
            <div class="px-6 py-4.5 border-b border-gray-100 dark:border-white/5 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-gray-50/50 dark:bg-white/5">
                <div>
                    <h2 class="text-lg font-black text-gray-900 dark:text-white">
                        Corte {{ $this->corte->folio }}
                    </h2>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-1">
                        Rango: {{ \Carbon\Carbon::parse($this->corte->fechaInicio)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($this->corte->fechaFin)->format('d/m/Y') }}
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <x-status-badge :status="$this->corte->estado === 'confirmado' ? 'Activo' : 'Inactiva'" />
                    
                    @if($this->corte->estado === 'confirmado')
                        <x-button wire:click="imprimirPdf" variant="secondary" class="!py-2 !px-4 text-xs">
                            <i class="fa-solid fa-print mr-2"></i>
                            Imprimir PDF
                        </x-button>
                    @elseif($this->todasConfirmadas)
                        <x-button wire:click="$dispatch('open-modal', 'confirm-corte-general')" variant="primary" class="!py-2 !px-4 text-xs">
                            <i class="fa-solid fa-check-double mr-2"></i>
                            Confirmar Corte
                        </x-button>
                    @else
                        <span class="text-xs font-bold uppercase tracking-wider text-gray-400 dark:text-gray-500">
                            Confirme todas las cuadrillas para habilitar el corte
                        </span>
                    @endif
                </div>
            </div>

            <!-- Tabla de Cuadrillas -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/50 dark:bg-white/5 border-b border-gray-100 dark:border-white/5 text-gray-400 dark:text-gray-500 text-[10px] font-black uppercase tracking-widest">
                            <th class="px-6 py-4">Cuadrilla</th>
                            <th class="px-6 py-4">Punto de Venta</th>
                            <th class="px-6 py-4 text-right">Toneladas</th>
                            <th class="px-6 py-4 text-right">Total Pagar</th>
                            <th class="px-6 py-4 text-center">Estatus</th>
                            <th class="px-6 py-4 text-right">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                        @foreach($this->corte->cuadrillas as $cuadrilla)
                            <tr class="hover:bg-gray-50/40 dark:hover:bg-white/5 transition-colors duration-150">
                                <td class="px-6 py-4.5 whitespace-nowrap flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-green-50 dark:bg-green-950/40 border border-green-100/50 dark:border-green-800/30 flex items-center justify-center font-bold text-xs text-green-700 dark:text-green-400 shrink-0">
                                        {{ substr(trim($cuadrilla->cuadrillaNombre), 0, 1) }}
                                    </div>
                                    <div class="font-bold text-gray-900 dark:text-white">
                                        {{ $cuadrilla->cuadrillaNombre }}
                                    </div>
                                </td>
                                <td class="px-6 py-4.5 whitespace-nowrap text-gray-600 dark:text-gray-400 font-semibold text-xs">
                                    {{ $cuadrilla->puntoVentaNombre }}
                                </td>
                                <td class="px-6 py-4.5 whitespace-nowrap font-mono font-bold text-gray-700 dark:text-gray-300 text-right">
                                    {{ number_format($cuadrilla->totalToneladas, 3) }}
                                </td>
                                <td class="px-6 py-4.5 whitespace-nowrap font-mono font-bold text-gray-900 dark:text-white text-right">
                                    ${{ number_format($cuadrilla->totalMonto, 2) }}
                                </td>
                                <td class="px-6 py-4.5 whitespace-nowrap text-center">
                                    @if($cuadrilla->confirmada)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black tracking-wider bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-400 uppercase">
                                            Confirmada
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-black tracking-wider bg-yellow-50 dark:bg-yellow-950/40 text-yellow-700 dark:text-yellow-400 uppercase">
                                            Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4.5 whitespace-nowrap text-right">
                                    <button 
                                        wire:click="openResumen({{ $cuadrilla->cuadrillaId }})" 
                                        class="text-gray-400 hover:text-green-600 dark:text-gray-500 dark:hover:text-green-400 transition-all duration-200 p-2.5 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5"
                                        title="Ver Resumen"
                                    >
                                        <i class="fa-solid fa-eye text-base"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

        </div>
    @endif

    <!-- Modals -->
    @livewire('corte.resumen-maniobras-modal')

    <!-- Confirmación de Corte General -->
    <x-confirm-modal 
        name="confirm-corte-general"
        title="Confirmar Corte de Liquidación"
        message="¿Estás seguro de confirmar el corte general? Una vez confirmado, todas las maniobras incluidas serán marcadas como liquidadas y este proceso será irreversible."
        confirm-action="confirmarCorteGeneral"
    />
</div>
