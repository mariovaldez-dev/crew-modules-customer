@props(['disabled' => false, 'options' => [], 'placeholder' => 'Seleccionar...'])

@php
    $formattedOptions = [];
    foreach($options as $val => $label) {
        $formattedOptions[] = ['value' => (string)$val, 'label' => $label];
    }
    
    $wireModel = $attributes->wire('model');
    $hasWireModel = $wireModel->directive() ? true : false;
@endphp

<div x-data="{ 
    open: false,
    placement: 'bottom',
    selected: @if($hasWireModel) @entangle($attributes->wire('model')){{ $wireModel->hasModifier('live') ? '.live' : '' }} @else '' @endif,
    options: {{ json_encode($formattedOptions) }},
    disabled: {{ $disabled ? 'true' : 'false' }},
    toggle() {
        if (this.disabled) return;
        if (!this.open) {
            const rect = this.$refs.button.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;
            this.placement = spaceBelow < 280 ? 'top' : 'bottom';
        }
        this.open = !this.open;
    },
    getOptionName(val) {
        const opt = this.options.find(o => o.value == val);
        return opt ? opt.label : val;
    },
    selectOption(val) {
        this.selected = val;
        this.open = false;
        
        // Ensure Livewire is notified immediately
        this.$dispatch('input', val);
    }
}"
class="relative w-full"
@click.away="open = false"
{!! $attributes->except(['options', 'placeholder', 'wire:model', 'wire:model.live', 'wire:model.blur', 'wire:key']) !!}
wire:key="{{ $attributes->get('wire:key', 'select-'.md5(json_encode($formattedOptions))) }}">

    <!-- Button Trigger -->
    <div x-ref="button" @click="toggle()"
        :class="{'opacity-50 cursor-not-allowed': disabled, 'border-green-500 ring-2 ring-green-500/50 dark:ring-green-500/30': open, 'border-gray-200 dark:border-white/10 hover:border-gray-300 dark:hover:border-white/20': !open && !disabled}"
        class="w-full h-12 rounded-2xl px-4 text-left flex items-center justify-between border border-gray-200 bg-white shadow-sm transition-all duration-200 cursor-pointer text-gray-900"
        style="background-color: #ffffff; border-radius: 1rem; border: 1px solid #e5e7eb;">
        
        <template x-if="!selected && selected !== '' && selected !== 0">
            <span class="text-sm text-gray-400 dark:text-gray-500 truncate" x-text="getOptionName('') || '{{ $placeholder }}'"></span>
        </template>
        <template x-if="selected || selected === '' || selected === 0">
            <span class="text-sm font-medium truncate" x-text="getOptionName(selected)"></span>
        </template>

        <i class="fa-solid fa-chevron-down text-gray-400 text-xs transition-transform duration-200 shrink-0 ml-2" :class="{'rotate-180': open}"></i>
    </div>

    <!-- Dropdown -->
    <div x-show="open" 
        x-transition:enter="transition ease-out duration-100"
        x-transition:enter-start="opacity-0 scale-95"
        x-transition:enter-end="opacity-100 scale-100"
        :class="placement === 'bottom' ? 'top-full mt-2' : 'bottom-full mb-2'"
        :style="placement === 'top' ? 'transform-origin: bottom;' : 'transform-origin: top;'"
        class="absolute z-50 w-full bg-white dark:bg-[#1A2227] border border-gray-100 dark:border-white/10 rounded-2xl shadow-xl overflow-hidden py-2"
        style="display: none;"
        x-cloak>
        <div class="max-h-60 overflow-y-auto custom-scrollbar">
            <template x-for="option in options" :key="option.value">
                <button
                    @click="selectOption(option.value)"
                    type="button"
                    class="w-full text-left px-4 py-2.5 text-sm hover:bg-gray-50 dark:hover:bg-white/5 transition-colors flex items-center"
                    :class="selected == option.value ? 'text-green-600 dark:text-green-400 font-black bg-green-50 dark:bg-green-500/10' : 'text-gray-700 dark:text-gray-300 font-medium'">
                    <span x-text="option.label" class="truncate pr-4 flex-1 text-left"></span>
                    <template x-if="selected == option.value">
                        <i class="fa-solid fa-check text-xs text-green-500 shrink-0"></i>
                    </template>
                </button>
            </template>
        </div>
    </div>
</div>
