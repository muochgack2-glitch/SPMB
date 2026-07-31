@props(['padding' => true, 'shadow' => 'sm'])

@php
    $shadowClasses = [
        'none' => '',
        'sm' => 'shadow-sm',
        'md' => 'shadow-md',
        'lg' => 'shadow-lg',
        'xl' => 'shadow-xl',
    ];
    
    $classes = "bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 transition-all duration-200 " . ($shadowClasses[$shadow] ?? 'shadow-sm');
    
    if ($padding) {
        $classes .= ' p-6';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</div>
