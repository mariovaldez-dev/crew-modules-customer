@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full h-12 border-gray-200 dark:border-white/10 bg-white dark:bg-[#1E293B] rounded-2xl px-4 text-sm text-gray-900 dark:text-white focus:border-green-500 focus:ring-0 shadow-sm transition-all duration-200 disabled:bg-gray-50 dark:disabled:bg-white/5 disabled:text-gray-500 placeholder:text-gray-400 dark:placeholder:text-gray-500']) !!}>
