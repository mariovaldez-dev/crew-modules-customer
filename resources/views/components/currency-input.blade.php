@props(['disabled' => false, 'id' => null])

<div class="relative w-full">
    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
        <span class="text-gray-500 dark:text-gray-400 sm:text-sm">$</span>
    </div>
    <input 
        type="number" 
        step="0.01" 
        min="0"
        {{ $disabled ? 'disabled' : '' }} 
        {{ $id ? "id=$id" : '' }}
        {!! $attributes->merge(['class' => 'w-full h-12 border-gray-200 dark:border-white/10 bg-white dark:bg-[#1E293B] rounded-2xl pl-8 pr-4 text-sm text-gray-900 dark:text-white focus:border-green-500 focus:ring-0 shadow-sm transition-all duration-200 disabled:bg-gray-50 dark:disabled:bg-white/5 disabled:text-gray-500 placeholder:text-gray-400 dark:placeholder:text-gray-500']) !!}
    >
</div>
