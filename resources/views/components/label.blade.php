@props(['value' => null])

<label {{ $attributes->merge(['class' => 'block font-bold text-[11px] uppercase tracking-widest text-gray-400 dark:text-gray-500 mb-2']) }}>
    {{ $value ?? $slot }}
</label>
