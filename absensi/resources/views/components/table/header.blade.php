@props(['sortable' => false, 'direction' => null])

@php
    $classes = "px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider";
    
    if ($sortable) {
        $classes .= " cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-800 select-none";
    }
@endphp

<th {{ $attributes->merge(['class' => $classes]) }}>
    <div class="flex items-center space-x-1">
        <span>{{ $slot }}</span>
        
        @if($sortable)
            <div class="flex flex-col">
                <i class="fas fa-caret-up text-xs {{ $direction === 'asc' ? 'text-primary-500' : 'text-gray-400' }} -mb-1"></i>
                <i class="fas fa-caret-down text-xs {{ $direction === 'desc' ? 'text-primary-500' : 'text-gray-400' }}"></i>
            </div>
        @endif
    </div>
</th>
