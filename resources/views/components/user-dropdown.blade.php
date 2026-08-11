@php
    $user = auth()->user();
    $name = $user->name ?? 'Usuario';
    $initials = collect(explode(' ', trim($name)))
        ->filter()
        ->map(fn($segment) => mb_substr($segment, 0, 1))
        ->take(2)
        ->join('');
    
    $contexto = session('usuario_contexto');
    $rolNombre = isset($contexto) && method_exists($contexto, 'isAdministradorManiobras') && $contexto->isAdministradorManiobras() 
        ? 'Administrador de Maniobras' 
        : 'Coordinadora de Almacén';
@endphp

<div x-data="{ open: false }" class="relative" @click.outside="open = false">

    {{-- Trigger: Perfil Completo --}}
    <button @click="open = !open"
        class="bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/10 group flex items-center gap-3 pl-1.5 pr-4 py-1.5 rounded-2xl hover:bg-gray-50 dark:hover:bg-white/5 transition-all duration-200 focus:outline-none shadow-sm cursor-pointer">

        {{-- Avatar --}}
        <div class="w-9 h-9 rounded-xl bg-green-600 dark:bg-green-700 flex items-center justify-center shadow-md shadow-green-900/20 group-hover:scale-105 transition-transform shrink-0">
            <span class="text-xs font-black text-white uppercase tracking-wider">{{ $initials }}</span>
        </div>

        {{-- Texto (Nombre & Rol) --}}
        <div class="hidden sm:flex flex-col items-start leading-tight max-w-[140px] text-left">
            <span class="text-[13px] font-bold text-gray-900 dark:text-white uppercase tracking-tight truncate w-full">{{ $name }}</span>
            <span class="text-[10px] font-semibold text-green-600 dark:text-green-400 truncate w-full">
                {{ $user->email ?? $rolNombre }}
            </span>
        </div>

        {{-- Icono Chevron --}}
        <i class="fa-solid fa-chevron-down text-xs text-gray-400 group-hover:text-gray-600 dark:text-gray-500 dark:group-hover:text-gray-300 transition-transform duration-200" :class="{'rotate-180': open}"></i>
    </button>

    {{-- Dropdown Menu --}}
    <div x-show="open" 
        style="display: none;"
        class="absolute right-0 mt-2 w-64 bg-white dark:bg-[#131B20] border border-gray-100 dark:border-white/10 rounded-2xl shadow-xl shadow-black/10 dark:shadow-green-900/10 z-50 p-2 overflow-hidden"
        x-cloak>

        {{-- Encabezado del Menú (Info Usuario) --}}
        <div class="px-3 py-2.5 border-b border-gray-100 dark:border-white/5 mb-1">
            <p class="text-xs font-black text-gray-900 dark:text-white uppercase truncate">{{ $name }}</p>
            <p class="text-[11px] text-green-600 dark:text-green-400 font-semibold truncate mt-0.5">{{ $rolNombre }}</p>
            @if(isset($contexto->zonaNombre))
                <p class="text-[10px] text-gray-400 dark:text-gray-500 font-medium truncate mt-0.5">
                    <i class="fa-solid fa-location-dot text-[9px] mr-1"></i>{{ $contexto->zonaNombre }}
                </p>
            @endif
        </div>

        {{-- Opción: Cerrar Sesión --}}
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="group flex items-center gap-3 w-full px-3 py-2.5 rounded-xl text-xs font-bold text-gray-700 dark:text-gray-200 hover:text-red-600 dark:hover:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all duration-150 cursor-pointer">
                <div class="w-8 h-8 rounded-lg bg-gray-100 dark:bg-white/5 group-hover:bg-red-100 dark:group-hover:bg-red-500/20 flex items-center justify-center text-gray-500 dark:text-gray-400 group-hover:text-red-600 dark:group-hover:text-red-400 transition-colors shrink-0">
                    <i class="fa-solid fa-right-from-bracket text-xs"></i>
                </div>
                Cerrar sesión
            </button>
        </form>
    </div>
</div>