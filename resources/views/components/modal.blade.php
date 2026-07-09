@props(['name', 'title', 'maxWidth' => '2xl'])

@php
$maxWidthClass = match ($maxWidth) {
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    default => 'sm:max-w-2xl',
};
@endphp

<div
    x-data="{ 
        show: false,
        checkName(detail) {
            const name = '{{ $name }}';
            return detail === name || (Array.isArray(detail) && detail[0] === name) || (detail && detail.name === name);
        }
    }"
    x-show="show"
    x-on:open-modal.window="checkName($event.detail) ? show = true : null"
    x-on:close-modal.window="checkName($event.detail) ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    style="display: none;"
    class="fixed inset-0 z-[100] overflow-y-auto px-4 py-6 sm:px-0"
>
    <!-- Backdrop -->
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-gray-900/75"
        x-on:click="show = false"
    ></div>

    <!-- Modal Panel -->
    <div
        x-show="show"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        class="mb-6 bg-white dark:bg-[#131B20] rounded-3xl overflow-hidden shadow-2xl transform transition-all sm:w-full sm:mx-auto {{ $maxWidthClass }} relative border border-gray-100 dark:border-white/5"
    >
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/5 flex items-center justify-between bg-gray-50/50 dark:bg-white/5">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                {{ $title }}
            </h3>
            <button x-on:click="show = false" class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors">
                <i class="fa-solid fa-xmark text-xl"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="px-6 py-4 bg-white dark:bg-[#131B20]">
            {{ $slot }}
        </div>

        <!-- Footer -->
        @if (isset($footer))
            <div class="px-6 py-4 bg-gray-50 dark:bg-white/5 border-t border-gray-100 dark:border-white/5 flex justify-end gap-3 rounded-b-3xl">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
