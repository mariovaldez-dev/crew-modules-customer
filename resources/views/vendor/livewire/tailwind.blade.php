@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Navegación de Páginas" class="flex flex-col sm:flex-row items-center justify-between gap-4">
        <!-- Información de resultados -->
        <div class="text-xs text-gray-500 dark:text-gray-400 font-medium">
            Mostrando 
            <span class="font-bold text-gray-900 dark:text-white">{{ $paginator->firstItem() }}</span>
            a
            <span class="font-bold text-gray-900 dark:text-white">{{ $paginator->lastItem() }}</span>
            de
            <span class="font-bold text-gray-900 dark:text-white">{{ $paginator->total() }}</span>
            resultados
        </div>

        <!-- Botones de Paginación -->
        <div class="inline-flex items-center gap-1.5 shrink-0">
            {{-- Botón Anterior --}}
            @if ($paginator->onFirstPage())
                <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-gray-100 dark:bg-white/5 text-gray-300 dark:text-gray-600 cursor-not-allowed text-xs">
                    <i class="fa-solid fa-chevron-left"></i>
                </span>
            @else
                <button 
                    type="button"
                    wire:click="previousPage('{{ $paginator->getPageName() }}')" 
                    wire:loading.attr="disabled"
                    class="w-9 h-9 rounded-xl flex items-center justify-center bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-700 dark:text-gray-200 transition-all duration-150 text-xs focus:outline-none focus:ring-2 focus:ring-green-500/50"
                    title="Página Anterior"
                >
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
            @endif

            {{-- Números de Página --}}
            @foreach ($elements as $element)
                {{-- Separador "..." --}}
                @if (is_string($element))
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-400 text-xs font-bold">...</span>
                @endif

                {{-- Links de páginas --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-green-600 text-white font-bold text-xs shadow-sm shadow-green-600/30">
                                {{ $page }}
                            </span>
                        @else
                            <button 
                                type="button"
                                wire:click="gotoPage({{ $page }}, '{{ $paginator->getPageName() }}')" 
                                wire:loading.attr="disabled"
                                class="w-9 h-9 rounded-xl flex items-center justify-center bg-gray-100 hover:bg-gray-200 dark:bg-white/5 dark:hover:bg-white/10 text-gray-700 dark:text-gray-300 font-bold text-xs transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-green-500/50"
                            >
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Botón Siguiente --}}
            @if ($paginator->hasMorePages())
                <button 
                    type="button"
                    wire:click="nextPage('{{ $paginator->getPageName() }}')" 
                    wire:loading.attr="disabled"
                    class="w-9 h-9 rounded-xl flex items-center justify-center bg-gray-100 hover:bg-gray-200 dark:bg-white/10 dark:hover:bg-white/20 text-gray-700 dark:text-gray-200 transition-all duration-150 text-xs focus:outline-none focus:ring-2 focus:ring-green-500/50"
                    title="Página Siguiente"
                >
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            @else
                <span class="w-9 h-9 rounded-xl flex items-center justify-center bg-gray-100 dark:bg-white/5 text-gray-300 dark:text-gray-600 cursor-not-allowed text-xs">
                    <i class="fa-solid fa-chevron-right"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
