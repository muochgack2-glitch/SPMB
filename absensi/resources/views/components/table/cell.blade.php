@props(['align' => 'left'])

@php
    $alignClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right',
    ];
    
    $classes = "px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100 " . ($alignClasses[$align] ?? $alignClasses['left']);
@endphp

<td {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</td>
