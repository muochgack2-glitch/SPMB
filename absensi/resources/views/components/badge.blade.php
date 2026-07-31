@props([
    'variant' => 'default',
    'size' => 'md',
    'dot' => false
])

@php
    $variantClasses = [
        'default' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300',
        'primary' => 'bg-primary-100 text-primary-800 dark:bg-primary-900/30 dark:text-primary-400',
        'success' => 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400',
        'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/30 dark:text-yellow-400',
        'danger' => 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-400',
        'info' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400',
    ];
    
    $sizeClasses = [
        'sm' => 'px-2 py-0.5 text-xs',
        'md' => 'px-2.5 py-1 text-xs',
        'lg' => 'px-3 py-1.5 text-sm',
    ];
    
    $dotColors = [
        'default' => 'bg-gray-400',
        'primary' => 'bg-primary-500',
        'success' => 'bg-green-500',
        'warning' => 'bg-yellow-500',
        'danger' => 'bg-red-500',
        'info' => 'bg-blue-500',
    ];
    
    $classes = "inline-flex items-center font-medium rounded-full";
    $classes .= ' ' . ($variantClasses[$variant] ?? $variantClasses['default']);
    $classes .= ' ' . ($sizeClasses[$size] ?? $sizeClasses['md']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    @if($dot)
        <span class="w-1.5 h-1.5 rounded-full mr-1.5 {{ $dotColors[$variant] ?? $dotColors['default'] }}"></span>
    @endif
    
    {{ $slot }}
</span>
