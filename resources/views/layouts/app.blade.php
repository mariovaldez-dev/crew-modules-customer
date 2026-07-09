<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Módulo de Operaciones</title>
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.png') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <!-- Tailwind CSS via Play CDN -->
    <script src="https://cdn.tailwindcss.com?plugins=forms"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        green: {
                            50: '#f4f7f5',
                            100: '#e3ece6',
                            200: '#c7d8ce',
                            300: '#9ebba9',
                            400: '#749983',
                            500: '#4A7C59',
                            600: '#1f7a2f',
                            700: '#2a4430',
                            800: '#1d2f21',
                            900: '#082312',
                        }
                    }
                }
            }
        }
    </script>
    <script>
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style type="text/tailwindcss">
        @layer base {
            [x-cloak] { display: none !important; }
            font-family: 'Inter', sans-serif;

            select {
                -webkit-appearance: none !important;
                -moz-appearance: none !important;
                appearance: none !important;
                background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
                background-position: right 0.75rem center !important;
                background-repeat: no-repeat !important;
                background-size: 1.25rem 1.25rem !important;
                padding-right: 2.5rem !important;
            }
            select:focus {
                outline: none !important;
                @apply ring-1 ring-green-500 border-green-500;
            }

            .custom-scrollbar::-webkit-scrollbar {
                width: 6px;
            }
            .custom-scrollbar::-webkit-scrollbar-track {
                @apply bg-gray-50 rounded-full;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb {
                @apply bg-gray-300 rounded-full;
            }
            .custom-scrollbar::-webkit-scrollbar-thumb:hover {
                @apply bg-gray-400;
            }

            input::-webkit-outer-spin-button,
            input::-webkit-inner-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            input[type=number] {
                -moz-appearance: textfield;
            }

            @media screen and (max-width: 768px) {
                input, select, textarea {
                    font-size: 16px !important;
                }
            }
        }

        .nav-link {
            @apply flex items-center gap-3 px-4 py-3.5 text-sm font-medium rounded-2xl transition-all duration-200;
        }
        .nav-link.active {
            @apply bg-green-50 text-green-700 dark:bg-green-950/40 dark:text-green-300 font-bold;
        }
        .nav-link:not(.active) {
            @apply text-gray-500 hover:bg-gray-50 hover:text-gray-900 dark:text-gray-400 dark:hover:bg-white/5 dark:hover:text-white;
        }
    </style>
    @livewireStyles
    @stack('styles')
</head>

<body class="font-sans antialiased text-gray-900 dark:text-gray-100 bg-[#f4f7f5] dark:bg-[#0B0F12] flex h-screen overflow-hidden transition-colors duration-300" x-data="{ sidebarOpen: false }">

    @auth
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/80 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" x-cloak></div>

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
                <button @click="sidebarOpen = false" class="ml-auto text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 lg:hidden">
                    <i class="fa-solid fa-xmark text-xl"></i>
                </button>
            </div>

            <!-- Navigation -->
            @php
                $context = session()->get('usuario_contexto');
                $rol = $context ? $context->tipo : (auth()->user()?->rol === 'AM' ? 'AM' : 'CO');
            @endphp
            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1.5 custom-scrollbar">
                
                <div class="px-4 text-[10px] font-black uppercase text-gray-400 dark:text-gray-500 tracking-widest mb-2 mt-4 first:mt-0">Principal</div>
                
                <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-house w-5 text-center"></i>
                    <span>Inicio</span>
                </a>
                
                <div class="px-4 text-[10px] font-black uppercase text-gray-400 dark:text-gray-500 tracking-widest mb-2 mt-6">Gestión</div>

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

                <div class="px-4 text-[10px] font-black uppercase text-gray-400 dark:text-gray-500 tracking-widest mb-2 mt-6">Operación</div>

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

            <!-- User Footer -->
            <div class="p-4 border-t border-gray-100 dark:border-white/5 shrink-0">
                <div class="flex items-center gap-3 px-3 py-3 rounded-2xl bg-gray-50 dark:bg-white/5 border border-gray-100 dark:border-white/5 text-gray-900 dark:text-white">
                    <div class="w-9 h-9 rounded-full bg-green-600 text-white flex items-center justify-center font-bold text-sm shrink-0">
                        {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-bold truncate">{{ auth()->user()->name ?? 'Usuario' }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 truncate">
                            {{ auth()->user()->rol === 'AM' ? 'Admin. Maniobras' : (auth()->user()->rol === 'CO' ? 'Coord. Almacén' : 'Operador') }}
                        </p>
                    </div>
                </div>
            </div>
        </aside>
    @endauth

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#F3F5F8] dark:bg-[#0B0F13] transition-colors duration-300">
        
        @auth
        <!-- Topbar -->
        <header class="h-20 bg-white dark:bg-[#131B20] border-b border-gray-200 dark:border-white/10 flex items-center justify-between px-4 lg:px-8 shrink-0 z-10 shadow-sm transition-colors duration-300">
            <div class="flex items-center gap-4">
                <!-- Sidebar Toggle (Mobile) -->
                <button @click="sidebarOpen = true" class="p-2 -ml-2 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 lg:hidden">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white hidden sm:block">
                    @yield('title', 'Dashboard')
                </h1>
            </div>

            <div class="flex items-center gap-4" x-data="{ 
                isDark: document.documentElement.classList.contains('dark'),
                toggleTheme() {
                    if (document.documentElement.classList.contains('dark')) {
                        document.documentElement.classList.remove('dark');
                        localStorage.setItem('color-theme', 'light');
                        this.isDark = false;
                    } else {
                        document.documentElement.classList.add('dark');
                        localStorage.setItem('color-theme', 'dark');
                        this.isDark = true;
                    }
                }
            }">
                <!-- Toggle Theme Button -->
                <button @click="toggleTheme" class="p-2.5 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-all duration-200" title="Cambiar Tema">
                    <i class="fa-solid" :class="isDark ? 'fa-sun text-lg text-amber-500' : 'fa-moon text-lg'"></i>
                </button>
                <x-user-dropdown />
            </div>
        </header>
        @endauth

        <!-- Main Scrollable Area -->
        <main class="flex-1 overflow-y-auto custom-scrollbar p-4 lg:p-8">
            <div class="max-w-7xl mx-auto w-full">
                @yield('content')
                {{ $slot ?? '' }}
            </div>
        </main>
    </div>

    {{-- TOAST / SNACKBAR GLOBAL --}}
    <div x-data="{ 
            show: false, 
            message: '', 
            type: 'success',
            timer: null,
            init() {
                window.addEventListener('notify', (event) => {
                    this.show = false; 
                    setTimeout(() => {
                        this.message = event.detail.message;
                        this.type = event.detail.type || 'success';
                        this.show = true;
                        if (this.timer) clearTimeout(this.timer);
                        this.timer = setTimeout(() => this.show = false, 4500);
                    }, 50);
                });
            }
        }"
        class="fixed top-[90px] left-0 right-0 md:left-auto md:right-6 z-[100] px-4 pointer-events-none flex justify-center md:justify-end"
        x-cloak>

        <div x-show="show" x-transition:enter="transition ease-out duration-500"
            x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 -translate-y-4 scale-95"
            class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-[1.25rem] border backdrop-blur-xl shadow-[0_20px_50px_-12px_rgba(0,0,0,0.15)] flex items-stretch transition-all duration-300"
            :class="{
                'bg-white/90 border-green-100 shadow-green-900/5': type === 'success',
                'bg-white/90 border-red-100 shadow-red-900/5': type === 'error',
                'bg-white/90 border-blue-100 shadow-blue-900/5': type === 'info'
            }">

            <div class="w-1.5 shrink-0" :class="{
                'bg-green-500': type === 'success',
                'bg-red-500': type === 'error',
                'bg-blue-500': type === 'info'
            }"></div>

            <div class="flex-1 p-4 flex items-start gap-3">
                <div class="shrink-0 mt-0.5">
                    <template x-if="type === 'success'">
                        <div class="w-8 h-8 rounded-full bg-green-50 flex items-center justify-center text-green-600">
                            <i class="fa-solid fa-check"></i>
                        </div>
                    </template>
                    <template x-if="type === 'error'">
                        <div class="w-8 h-8 rounded-full bg-red-50 flex items-center justify-center text-red-600">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                    </template>
                </div>

                <div class="flex-1 min-w-0">
                    <h4 class="text-[13px] font-black uppercase tracking-widest leading-none mb-1" :class="{
                        'text-green-600': type === 'success',
                        'text-red-600': type === 'error',
                        'text-blue-600': type === 'info'
                    }" x-text="type === 'success' ? 'Éxito' : (type === 'error' ? 'Error' : 'Aviso')"></h4>
                    <p class="text-[14px] text-gray-700 font-medium leading-snug" x-text="message"></p>
                </div>

                <button @click="show = false" class="shrink-0 text-gray-300 hover:text-gray-500 transition-colors">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    </div>

    @livewireScripts
    <script>
        window.onbeforeunload = function () { window.scrollTo(0, 0); };
        if ('scrollRestoration' in history) { history.scrollRestoration = 'manual'; }
    </script>
    @stack('scripts')
</body>

</html>
