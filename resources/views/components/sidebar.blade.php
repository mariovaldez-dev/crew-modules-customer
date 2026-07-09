<!-- Sidebar -->
<aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-72 bg-white dark:bg-[#131B20] border-r border-gray-100 dark:border-white/5 flex flex-col transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 shadow-xl lg:shadow-none">
    <!-- Brand -->
    <div class="h-20 flex items-center px-6 border-b border-gray-100 dark:border-white/5 shrink-0">
        <div class="flex items-center gap-3">
            <div class="bg-green-50 dark:bg-green-950/30 p-2.5 rounded-2xl">
                <img src="{{ asset('favicon.png') }}" alt="Logo" class="h-6 w-6 object-contain" />
            </div>
            <div class="flex flex-col">
                <span class="text-[10px] font-black uppercase text-green-600 dark:text-green-400 tracking-[0.2em] leading-none mb-0.5">Módulo de</span>
                <span class="text-sm font-extrabold text-gray-900 dark:text-white leading-none">Operaciones</span>
            </div>
        </div>
        <!-- Close Button (Mobile) -->
        <button @click="sidebarOpen = false" class="ml-auto text-gray-400 hover:text-gray-650 dark:text-gray-500 dark:hover:text-gray-300 lg:hidden">
            <i class="fa-solid fa-xmark text-xl"></i>
        </button>
    </div>

    <!-- Navigation -->
    @php
        $context = session()->get('usuario_contexto');
        $rol = $context ? $context->tipo : (auth()->user()?->rol === 'AM' ? 'AM' : 'CO');
    @endphp
    <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 custom-scrollbar">
        
        <div class="px-4 text-[10px] font-black uppercase text-gray-500 dark:text-gray-400 tracking-widest mb-2 mt-4 first:mt-0">Principal</div>
        
        <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-house w-5 text-center"></i>
            <span>Inicio</span>
        </a>
        
        <div class="px-4 text-[10px] font-black uppercase text-gray-500 dark:text-gray-400 tracking-widest mb-2 mt-6">Gestión</div>

        @if($rol === 'AM')
            <a href="/maniobras" class="nav-link {{ request()->is('maniobras*') ? 'active' : '' }}">
                <i class="fa-solid fa-boxes-stacked w-5 text-center"></i>
                <span>Maniobras</span>
            </a>
        @endif
        
        <a href="/cuadrillas" class="nav-link {{ request()->is('cuadrillas*') ? 'active' : '' }}">
            <i class="fa-solid fa-users-gear w-5 text-center"></i>
            <span>Cuadrillas</span>
        </a>

        <a href="/tarifas" class="nav-link {{ request()->is('tarifas*') ? 'active' : '' }}">
            <i class="fa-solid fa-hand-holding-dollar w-5 text-center"></i>
            <span>Tarifas</span>
        </a>

        <div class="px-4 text-[10px] font-black uppercase text-gray-500 dark:text-gray-400 tracking-widest mb-2 mt-6">Operación</div>

        <a href="/registro-maniobras" class="nav-link {{ request()->is('registro-maniobras*') ? 'active' : '' }}">
            <i class="fa-solid fa-clipboard-list w-5 text-center"></i>
            <span>Registro de Maniobras</span>
        </a>

        @if($rol === 'CO')
            <a href="/corte-liquidacion" class="nav-link {{ request()->is('corte-liquidacion*') ? 'active' : '' }}">
                <i class="fa-solid fa-file-invoice-dollar w-5 text-center"></i>
                <span>Corte de Liquidación</span>
            </a>
        @endif
    </nav>
</aside>
