<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Módulo de Operaciones</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300..800;1,300..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">

        <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Styles -->
    <script>
        function applyTheme() {
            if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }
        applyTheme();
        document.addEventListener('livewire:navigated', applyTheme);
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>

<body class="font-sans antialiased text-gray-900 dark:text-gray-100 bg-[#f4f7f5] dark:bg-[#0B0F12] flex h-screen overflow-hidden transition-colors duration-300" 
      x-data="{ 
          sidebarOpen: false,
          toggleSidebar() {
              this.sidebarOpen = !this.sidebarOpen;
          }
      }">

    @auth
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" x-transition.opacity class="fixed inset-0 z-40 bg-gray-900/80 lg:hidden" @click="toggleSidebar()" x-cloak></div>

        <!-- Sidebar -->
        <x-sidebar />
    @endauth

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#F3F5F8] dark:bg-[#0B0F13] transition-colors duration-300">
        
        @auth
        <!-- Topbar -->
        <header class="h-20 bg-white dark:bg-[#131B20] border-b border-gray-200 dark:border-white/10 flex items-center justify-between px-4 lg:px-8 shrink-0 z-10 shadow-sm transition-colors duration-300">
            <div class="flex items-center gap-4">
                <!-- Sidebar Toggle (Mobile & Desktop) -->
                <button @click="toggleSidebar()" class="p-2 -ml-2 rounded-xl text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-white/5 transition-colors" title="Alternar menú lateral">
                    <i class="fa-solid fa-bars text-xl"></i>
                </button>
                <h1 class="text-xl font-bold text-gray-800 dark:text-white hidden sm:block">
                    {{ $title ?? View::yieldContent('title') ?: 'Inicio' }}
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
                const handleNotify = (event) => {
                    let detail = event.detail;
                    if (Array.isArray(detail) && detail.length > 0) {
                        detail = detail[0];
                    }
                    const msg = typeof detail === 'string' ? detail : (detail?.message || '');
                    const typeVal = (typeof detail === 'object' && detail?.type) ? detail.type : 'success';

                    if (!msg) return;

                    this.show = false; 
                    setTimeout(() => {
                        this.message = msg;
                        this.type = typeVal;
                        this.show = true;
                        if (this.timer) clearTimeout(this.timer);
                        this.timer = setTimeout(() => this.show = false, 4500);
                    }, 50);
                };

                window.addEventListener('notify', handleNotify);
                if (window.Livewire) {
                    window.Livewire.on('notify', (data) => handleNotify({ detail: data }));
                } else {
                    document.addEventListener('livewire:init', () => {
                        window.Livewire.on('notify', (data) => handleNotify({ detail: data }));
                    });
                }
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
            class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-[1.25rem] border shadow-[0_20px_50px_-12px_rgba(0,0,0,0.15)] flex items-stretch transition-all duration-300 dark:border-white/10"
            :class="{
                'bg-white/90 dark:bg-[#1A2227]/90 border-green-100 shadow-green-900/5 dark:shadow-green-900/20': type === 'success',
                'bg-white/90 dark:bg-[#1A2227]/90 border-red-100 shadow-red-900/5 dark:shadow-red-900/20': type === 'error',
                'bg-white/90 dark:bg-[#1A2227]/90 border-blue-100 shadow-blue-900/5 dark:shadow-blue-900/20': type === 'info'
            }">

            <div class="w-1.5 shrink-0" :class="{
                'bg-green-500': type === 'success',
                'bg-red-500': type === 'error',
                'bg-blue-500': type === 'info'
            }"></div>

            <div class="flex-1 p-4 flex items-start gap-3">
                <div class="shrink-0 mt-0.5">
                    <template x-if="type === 'success'">
                        <div class="w-8 h-8 rounded-full bg-green-50 dark:bg-green-500/10 flex items-center justify-center text-green-600 dark:text-green-400">
                            <i class="fa-solid fa-check"></i>
                        </div>
                    </template>
                    <template x-if="type === 'error'">
                        <div class="w-8 h-8 rounded-full bg-red-50 dark:bg-red-500/10 flex items-center justify-center text-red-600 dark:text-red-400">
                            <i class="fa-solid fa-triangle-exclamation"></i>
                        </div>
                    </template>
                </div>

                <div class="flex-1 min-w-0">
                    <h4 class="text-[13px] font-black uppercase tracking-widest leading-none mb-1" :class="{
                        'text-green-600 dark:text-green-400': type === 'success',
                        'text-red-600 dark:text-red-400': type === 'error',
                        'text-blue-600 dark:text-blue-400': type === 'info'
                    }" x-text="type === 'success' ? 'Éxito' : (type === 'error' ? 'Error' : 'Aviso')"></h4>
                    <p class="text-[14px] text-gray-700 dark:text-gray-300 font-medium leading-snug" x-text="message"></p>
                </div>

                <button @click="show = false" class="shrink-0 text-gray-300 hover:text-gray-500 dark:hover:text-white transition-colors">
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
