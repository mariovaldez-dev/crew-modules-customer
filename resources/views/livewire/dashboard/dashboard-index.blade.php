<div class="space-y-8 py-4">
    <!-- Header Page -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white">
                Bienvenido de nuevo, {{ explode(' ', trim(auth()->user()->name))[0] }}
            </h1>
            <p class="text-xs text-gray-400 dark:text-gray-500 font-bold uppercase tracking-widest mt-1">
                Zona: {{ $zona }} &middot; Rol: {{ $rol === 'AM' ? 'Administrador de Maniobras' : 'Coordinadora de Almacén' }}
            </p>
        </div>
        
        <!-- Action Buttons scoped by role -->
        <div class="flex items-center gap-2">
            @if($rol === 'CO')
                <a href="{{ route('registro-maniobras.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-green-600 hover:bg-green-700 text-white text-sm font-bold shadow-md shadow-green-900/10 hover:shadow-lg transition-all duration-200">
                    <i class="fa-solid fa-plus text-xs"></i>
                    Registrar Maniobra
                </a>
                <a href="{{ route('corte-liquidacion.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-50 dark:hover:bg-white/10 text-gray-700 dark:text-white text-sm font-bold transition-all duration-200">
                    <i class="fa-solid fa-file-invoice-dollar text-xs"></i>
                    Generar Corte
                </a>
            @else
                <a href="{{ route('maniobras.index') }}" class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-green-600 hover:bg-green-700 text-white text-sm font-bold shadow-md shadow-green-900/10 hover:shadow-lg transition-all duration-200">
                    <i class="fa-solid fa-plus text-xs"></i>
                    Nueva Maniobra
                </a>
            @endif
        </div>
    </div>

    <!-- Small Stat Cards Grid -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card 1: Cuadrillas -->
        <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Cuadrillas</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-green-50 dark:bg-green-950/40 text-[10px] font-black text-green-700 dark:text-green-400 tracking-wider">
                    Activas
                </span>
            </div>
            <div class="mt-4">
                <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $totalCuadrillas }}</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold mt-1">Registradas en tu zona</p>
            </div>
        </div>

        <!-- Card 2: Maniobras Catálogo / Tons -->
        @if($rol === 'AM')
            <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Tipos Maniobra</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-blue-50 dark:bg-blue-950/40 text-[10px] font-black text-blue-700 dark:text-blue-400 tracking-wider">
                        Catálogo
                    </span>
                </div>
                <div class="mt-4">
                    <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $totalManiobras }}</p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold mt-1">Conceptos autorizados</p>
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Tons registradas</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-50 dark:bg-emerald-950/40 text-[10px] font-black text-emerald-700 dark:text-emerald-400 tracking-wider">
                        Volumen
                    </span>
                </div>
                <div class="mt-4">
                    <p class="text-3xl font-black text-gray-900 dark:text-white">
                        {{ number_format($toneladasTotales, 1) }}t
                    </p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold mt-1">Acumulado del mes</p>
                </div>
            </div>
        @endif

        <!-- Card 3: En Proceso -->
        <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">En proceso</span>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-amber-50 dark:bg-amber-950/40 text-[10px] font-black text-amber-700 dark:text-amber-400 tracking-wider">
                    Pendiente
                </span>
            </div>
            <div class="mt-4">
                <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $maniobrasEnProceso }}</p>
                <p class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold mt-1">Maniobras sin liquidar</p>
            </div>
        </div>

        <!-- Card 4: Corte / Liquidadas -->
        @if($rol === 'CO')
            <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Corte Actual</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/40 text-[10px] font-black text-purple-700 dark:text-purple-400 tracking-wider">
                        Borrador
                    </span>
                </div>
                <div class="mt-4">
                    @if($corteActual)
                        <p class="text-xl font-extrabold text-purple-600 dark:text-purple-400 truncate">
                            {{ $corteActual->folio }}
                        </p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold mt-1">
                            {{ count($corteActual->cuadrillas) }} cuadrillas listas
                        </p>
                    @else
                        <p class="text-lg font-black text-gray-400 dark:text-gray-500">Sin borrador</p>
                        <p class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold mt-1">Listo para nuevo corte</p>
                    @endif
                </div>
            </div>
        @else
            <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl p-6 shadow-sm hover:shadow-md transition-all duration-300 flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-gray-400 dark:text-gray-500 uppercase tracking-wider">Liquidadas</span>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-purple-50 dark:bg-purple-950/40 text-[10px] font-black text-purple-700 dark:text-purple-400 tracking-wider">
                        Completado
                    </span>
                </div>
                <div class="mt-4">
                    <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $maniobrasLiquidadas }}</p>
                    <p class="text-[11px] text-gray-400 dark:text-gray-500 font-semibold mt-1">Maniobras cerradas</p>
                </div>
            </div>
        @endif
    </div>

    <!-- Quick Navigation Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Card 1: Maniobras Catálogo (Solo AM) -->
        @if($rol === 'AM')
            <a href="{{ route('maniobras.index') }}" class="group bg-white dark:bg-[#131B20] border border-gray-100/80 dark:border-white/5 rounded-3xl p-6 shadow-sm hover:shadow-md hover:border-gray-200 dark:hover:border-white/10 transition-all duration-300 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-green-50 dark:bg-green-950/30 rounded-2xl flex items-center justify-center text-green-600 dark:text-green-400 shrink-0">
                        <i class="fa-solid fa-boxes-stacked text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-green-700 dark:group-hover:text-green-400 transition-colors">Catálogo de Maniobras</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Conceptos autorizados y estatus.</p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-gray-300 dark:text-gray-700 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors"></i>
            </a>
        @endif

        <!-- Card 2: Gestión de Cuadrillas -->
        <a href="{{ route('cuadrillas.index') }}" class="group bg-white dark:bg-[#131B20] border border-gray-100/80 dark:border-white/5 rounded-3xl p-6 shadow-sm hover:shadow-md hover:border-gray-200 dark:hover:border-white/10 transition-all duration-300 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-50 dark:bg-blue-950/30 rounded-2xl flex items-center justify-center text-blue-600 dark:text-blue-400 shrink-0">
                    <i class="fa-solid fa-users-gear text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-blue-700 dark:group-hover:text-blue-400 transition-colors">Cuentas de Cuadrillas</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Equipos y configuración de tarifas.</p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-gray-300 dark:text-gray-700 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors"></i>
        </a>

        <!-- Card 3: Registro de Maniobras -->
        <a href="{{ route('registro-maniobras.index') }}" class="group bg-white dark:bg-[#131B20] border border-gray-100/80 dark:border-white/5 rounded-3xl p-6 shadow-sm hover:shadow-md hover:border-gray-200 dark:hover:border-white/10 transition-all duration-300 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-purple-50 dark:bg-purple-950/30 rounded-2xl flex items-center justify-center text-purple-600 dark:text-purple-400 shrink-0">
                    <i class="fa-solid fa-clipboard-list text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-purple-700 dark:group-hover:text-purple-400 transition-colors">Bitácora de Maniobras</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Registro manual y consulta.</p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-gray-300 dark:text-gray-700 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors"></i>
        </a>

        <!-- Card 4: Tarifas (Consulta) -->
        <a href="{{ route('tarifas.index') }}" class="group bg-white dark:bg-[#131B20] border border-gray-100/80 dark:border-white/5 rounded-3xl p-6 shadow-sm hover:shadow-md hover:border-gray-200 dark:hover:border-white/10 transition-all duration-300 flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-orange-50 dark:bg-orange-950/30 rounded-2xl flex items-center justify-center text-orange-600 dark:text-orange-400 shrink-0">
                    <i class="fa-solid fa-hand-holding-dollar text-lg"></i>
                </div>
                <div>
                    <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-orange-700 dark:group-hover:text-orange-400 transition-colors">Consulta de Tarifas</h3>
                    <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Listado por cuadrilla y zona.</p>
                </div>
            </div>
            <i class="fa-solid fa-chevron-right text-gray-300 dark:text-gray-700 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors"></i>
        </a>

        <!-- Card 5: Corte de Liquidación (Solo CO) -->
        @if($rol === 'CO')
            <a href="{{ route('corte-liquidacion.index') }}" class="group bg-white dark:bg-[#131B20] border border-gray-100/80 dark:border-white/5 rounded-3xl p-6 shadow-sm hover:shadow-md hover:border-gray-200 dark:hover:border-white/10 transition-all duration-300 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <div class="w-12 h-12 bg-teal-50 dark:bg-teal-950/30 rounded-2xl flex items-center justify-center text-teal-600 dark:text-teal-400 shrink-0">
                        <i class="fa-solid fa-file-invoice-dollar text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white group-hover:text-teal-700 dark:group-hover:text-teal-400 transition-colors">Corte de Liquidación</h3>
                        <p class="text-xs text-gray-400 dark:text-gray-500 font-medium">Generación y firma de cortes.</p>
                    </div>
                </div>
                <i class="fa-solid fa-chevron-right text-gray-300 dark:text-gray-700 group-hover:text-gray-500 dark:group-hover:text-gray-400 transition-colors"></i>
            </a>
        @endif
    </div>

    <!-- Recent Table (Proptee style) -->
    <div class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/5 rounded-3xl p-6 shadow-sm space-y-6">
        <div class="flex items-center justify-between pb-2">
            <div>
                <h3 class="text-lg font-black text-gray-900 dark:text-white">Últimas Maniobras</h3>
                <p class="text-xs text-gray-400 dark:text-gray-500 font-semibold mt-0.5">Las operaciones más recientes registradas en tu zona.</p>
            </div>
            <a href="{{ route('registro-maniobras.index') }}" class="inline-flex items-center gap-1 text-xs font-bold text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300 transition-colors">
                Ver toda la bitácora
                <i class="fa-solid fa-chevron-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-white/5 text-gray-400 text-[10px] font-black uppercase tracking-widest">
                        <th class="pb-3 px-3">Cuadrilla</th>
                        <th class="pb-3 px-3">Folio / Fecha</th>
                        <th class="pb-3 px-3">Almacén</th>
                        <th class="pb-3 px-3">Concepto</th>
                        <th class="pb-3 px-3 text-right">Toneladas</th>
                        <th class="pb-3 px-3 text-right">Estado</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5 text-sm">
                    @forelse($recientesRegistros as $registro)
                        <tr class="hover:bg-gray-50/40 dark:hover:bg-white/5 transition-colors duration-150">
                            <!-- Cuadrilla (avatar style) -->
                            <td class="py-4 px-3 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-green-50 dark:bg-green-950/40 border border-green-100/50 dark:border-green-800/30 flex items-center justify-center font-bold text-xs text-green-700 dark:text-green-400 shrink-0">
                                    {{ substr(trim($registro->cuadrillaNombre), 0, 1) }}
                                </div>
                                <div class="font-bold text-gray-900 dark:text-white">
                                    {{ $registro->cuadrillaNombre }}
                                </div>
                            </td>
                            <!-- Folio / Fecha -->
                            <td class="py-4 px-3">
                                <div class="font-bold text-gray-700 dark:text-gray-300 text-xs">{{ $registro->folio }}</div>
                                <div class="text-[10px] text-gray-400 dark:text-gray-500 font-bold uppercase tracking-wider mt-0.5">
                                    {{ $registro->fecha->format('d/m/Y') }}
                                </div>
                            </td>
                            <!-- Almacén -->
                            <td class="py-4 px-3 text-gray-500 dark:text-gray-400 font-medium">{{ $registro->almacenNombre }}</td>
                            <!-- Concepto -->
                            <td class="py-4 px-3 text-gray-500 dark:text-gray-400">{{ $registro->tipoManiobraNombre }}</td>
                            <!-- Toneladas (Monospace) -->
                            <td class="py-4 px-3 text-right font-mono font-bold text-gray-800 dark:text-white">
                                {{ number_format($registro->toneladas, 3) }} t
                            </td>
                            <!-- Estado (Badge) -->
                            <td class="py-4 px-3 text-right">
                                <x-status-badge :status="$registro->estado" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-12 text-center text-gray-400 dark:text-gray-500">
                                <div class="flex flex-col items-center gap-2">
                                    <i class="fa-regular fa-folder-open text-4xl mb-2 text-gray-300 dark:text-gray-700"></i>
                                    <p class="font-bold">No hay registros recientes</p>
                                    <p class="text-xs">Las maniobras ingresadas aparecerán aquí automáticamente.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
