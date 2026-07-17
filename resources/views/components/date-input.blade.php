@props(['disabled' => false, 'id' => null])

<input 
    type="date" 
    {{ $disabled ? 'disabled' : '' }} 
    {{ $id ? "id=$id" : '' }}
    onclick="this.showPicker && this.showPicker()"
    onfocus="this.showPicker && this.showPicker()"
    {!! $attributes->merge(['class' => 'w-full h-12 border-gray-200 dark:border-white/10 bg-white dark:bg-[#1E293B] rounded-2xl px-4 text-sm text-gray-900 dark:text-white focus:border-green-500 focus:ring-2 focus:ring-green-500/50 dark:focus:ring-green-500/30 shadow-sm transition-all duration-300 disabled:bg-gray-50 dark:disabled:bg-white/5 disabled:text-gray-500 hover:border-gray-300 dark:hover:border-white/20 cursor-pointer']) !!}
>
