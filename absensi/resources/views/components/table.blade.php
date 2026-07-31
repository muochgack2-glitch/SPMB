@props([
    'striped' => true,
    'hoverable' => true
])

@php
    $classes = "min-w-full divide-y divide-gray-200 dark:divide-gray-700";
@endphp

<div class="overflow-x-auto">
    <table {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </table>
</div>
