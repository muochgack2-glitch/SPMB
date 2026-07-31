@props(['striped' => false, 'hover' => true])

@php
    $classes = "transition-colors duration-150";
    
    if ($hover) {
        $classes .= " hover:bg-gray-50 dark:hover:bg-gray-800/50";
    }
    
    if ($striped) {
        $classes .= " odd:bg-white even:bg-gray-50 dark:odd:bg-gray-900 dark:even:bg-gray-800/30";
    } else {
        $classes .= " bg-white dark:bg-gray-900";
    }
@endphp

<tr {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</tr>
