@props(['zona', 'cuadrillas'])

<div x-data="{ expanded: false }" class="bg-white dark:bg-[#131B20] rounded-3xl border border-gray-100 dark:border-white/5 overflow-hidden mb-4 shadow-sm">
    <button 
        x-on:click="expanded = !expanded" 
        class="w-full px-4 py-4 flex items-center justify-between bg-gray-50/50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition-colors focus:outline-none"
    >
        <div class="flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-green-50 dark:bg-green-950/40 text-green-700 dark:text-green-400 flex items-center justify-center">
                <i class="fa-solid fa-map-location-dot"></i>
            </div>
            <h3 class="text-base font-bold text-gray-900 dark:text-white">{{ $zona }}</h3>
            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-200/60 dark:bg-white/10 text-gray-700 dark:text-gray-300">
                {{ count($cuadrillas) }} Cuadrillas
            </span>
        </div>
        <div class="text-gray-400 dark:text-gray-500 transition-transform duration-300" :class="expanded ? 'rotate-180' : ''">
            <i class="fa-solid fa-chevron-down"></i>
        </div>
    </button>

    <div x-show="expanded" x-collapse>
        <div class="p-6 border-t border-gray-100 dark:border-white/5 bg-white dark:bg-[#131B20]">
            {{ $slot }}
        </div>
    </div>
</div>
