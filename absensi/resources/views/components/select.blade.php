@props([
    'label' => null,
    'name' => '',
    'value' => '',
    'placeholder' => 'Pilih...',
    'error' => null,
    'required' => false,
    'disabled' => false,
    'options' => [],
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
    
    <select
        name="{{ $name }}"
        id="{{ $name }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
        {{ $attributes->merge([
            'class' => 'block w-full rounded-lg border px-4 py-2.5 transition-colors duration-200 focus:ring-2 focus:ring-primary-500 focus:border-transparent disabled:bg-gray-100 disabled:cursor-not-allowed dark:bg-gray-800 dark:border-gray-600 dark:text-white ' .
            ($error ? 'border-red-300 text-red-900 focus:ring-red-500 dark:border-red-500' : 'border-gray-300 text-gray-900')
        ]) }}
    >
        @if($placeholder)
            <option value="">{{ $placeholder }}</option>
        @endif
        
        @foreach($options as $optionValue => $optionLabel)
            <option value="{{ $optionValue }}" {{ old($name, $value) == $optionValue ? 'selected' : '' }}>
                {{ $optionLabel }}
            </option>
        @endforeach
        
        {{ $slot }}
    </select>
    
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
