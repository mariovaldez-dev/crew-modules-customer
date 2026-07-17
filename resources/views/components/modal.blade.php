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
        loading: false,
        checkName(detail) {
            const name = '{{ $name }}';
            return detail === name || (Array.isArray(detail) && detail[0] === name) || (detail && detail.name === name);
        },
        init() {
            const setLoad = (val) => { this.loading = val; };
            if (window.Livewire) {
                window.Livewire.hook('request', ({ respond, succeed, fail }) => {
                    setLoad(true);
                    respond(() => setLoad(false));
                    succeed(() => setLoad(false));
                    fail(() => setLoad(false));
                });
            } else {
                document.addEventListener('livewire:init', () => {
                    window.Livewire.hook('request', ({ respond, succeed, fail }) => {
                        setLoad(true);
                        respond(() => setLoad(false));
                        succeed(() => setLoad(false));
                        fail(() => setLoad(false));
                    });
                });
            }
        }
    }"
    x-show="show"
    x-on:open-modal.window="checkName($event.detail) ? show = true : null"
    x-on:close-modal.window="checkName($event.detail) ? show = false : null"
    x-on:close.stop="!loading ? show = false : null"
    x-on:keydown.escape.window="!loading ? show = false : null"
    style="display: none;"
    class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6"
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
        x-on:click="!loading ? show = false : null"
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
        class="w-full bg-white dark:bg-[#131B20] rounded-3xl shadow-2xl shadow-black/20 dark:shadow-green-900/10 transform transition-all mx-auto {{ $maxWidthClass }} relative border border-gray-100 dark:border-white/5"
    >
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-100 dark:border-white/5 flex items-center justify-between bg-gray-50/50 dark:bg-white/5 rounded-t-3xl">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">
                {{ $title }}
            </h3>
            <button x-on:click="!loading ? show = false : null" 
                    class="text-gray-400 hover:text-gray-600 dark:text-gray-500 dark:hover:text-gray-300 transition-colors"
                    :class="loading ? 'opacity-50 cursor-not-allowed pointer-events-none' : ''">
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
