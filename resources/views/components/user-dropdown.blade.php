@php
    $name = auth()->user()->name ?? 'Usuario';
    $initials = collect(explode(' ', $name))
        ->map(fn($segment) => mb_substr($segment, 0, 1))
        ->take(2)
        ->join('');
@endphp

<div x-data="{ open: false }" class="relative" @click.outside="open = false">

    {{-- Trigger: Perfil Completo (Desktop) --}}
    <button @click="open = !open"
        class="bg-white group flex items-center gap-3 pl-1 pr-4 py-1 rounded-2xl hover:bg-gray-50 transition-all duration-200 focus:outline-none border border-transparent hover:border-gray-100">

        {{-- Avatar --}}
        <div
            class="w-9 h-9 rounded-xl bg-green-600 flex items-center justify-center shadow-lg shadow-green-600/20 group-hover:scale-105 transition-transform">
            <span class="text-xs font-black text-white uppercase tracking-tighter">{{ $initials }}</span>
        </div>

        {{-- Texto --}}
        <div class="hidden md:flex w-25 flex-col items-start leading-tight">
            <span class="text-[13px] font-black text-gray-900 uppercase tracking-tight">{{ $name }}</span>
            <span class="text-[10px] font-bold text-green-600 uppercase tracking-widest truncate">
                {{ auth()->user()->email }}
            </span>
        </div>

        {{-- Icono --}}
        <svg class="w-4 h-4 text-gray-400 group-hover:text-gray-600 transition-colors" :class="{'rotate-180': open}"
            fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7" />
        </svg>
    </button>

    {{-- Dropdown --}}
    <div x-show="open" x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 scale-95 -translate-y-1"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-95 -translate-y-1"
        class="absolute right-0 mt-3 bg-white border border-gray-100 rounded-lg shadow-2xl shadow-green-900/10 z-50 overflow-hidden"
        x-cloak>


        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit"
                class="group flex items-center gap-3 w-full px-4 py-3 rounded-2xl text-sm font-bold text-gray-500 hover:text-red-600 hover:bg-red-50 transition-all duration-200">
                <div
                    class="w-8 h-8 rounded-xl bg-gray-50 group-hover:bg-red-100 flex items-center justify-center transition-colors">
                    <svg class="w-4 h-4 text-gray-400 group-hover:text-red-600" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                </div>
                Cerrar sesión
            </button>
        </form>
    </div>
</div>