@props([
    'label' => null,
    'name' => '',
    'type' => 'text',
    'value' => '',
    'placeholder' => '',
    'error' => null,
    'icon' => null,
    'iconPosition' => 'left',
    'required' => false,
    'disabled' => false,
    'helper' => null
])

<div class="space-y-1">
    @if($label)
        <label for="{{ $name }}" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
            {{ $label }}
            @if($required)
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif
    
    <div class="relative">
        @if($icon && $iconPosition === 'left')
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas {{ $icon }} text-gray-400"></i>
            </div>
        @endif
        
        <input
            type="{{ $type }}"
            name="{{ $name }}"
            id="{{ $name }}"
            value="{{ old($name, $value) }}"
            placeholder="{{ $placeholder }}"
            @if($required) required @endif
            @if($disabled) disabled @endif
            {{ $attributes->merge([
                'class' => 'block w-full rounded-lg border transition-colors duration-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-400 ' .
                ($error ? 'border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 dark:border-red-500' : 'border-gray-300 text-gray-900 placeholder-gray-400') .
                ($icon && $iconPosition === 'left' ? ' pl-10' : '') .
                ($icon && $iconPosition === 'right' ? ' pr-10' : '') .
                ' ' . ($icon ? '' : 'px-4') . ' py-2.5'
            ]) }}
        >
        
        @if($icon && $iconPosition === 'right')
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <i class="fas {{ $icon }} text-gray-400"></i>
            </div>
        @endif
    </div>
    
    @if($helper && !$error)
        <p class="text-xs text-gray-500 dark:text-gray-400">{{ $helper }}</p>
    @endif
    
    @if($error)
        <p class="text-xs text-red-600 dark:text-red-400">{{ $error }}</p>
    @endif
    
    @error($name)
        <p class="text-xs text-red-600 dark:text-red-400">{{ $message }}</p>
    @enderror
</div>
