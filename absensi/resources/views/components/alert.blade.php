@props(['type' => 'info', 'message' => '', 'dismissible' => false])

@php
    $typeClasses = [
        'info' => 'bg-blue-50 dark:bg-blue-900/20 border-blue-200 dark:border-blue-800 text-blue-800 dark:text-blue-200',
        'success' => 'bg-green-50 dark:bg-green-900/20 border-green-200 dark:border-green-800 text-green-800 dark:text-green-200',
        'warning' => 'bg-yellow-50 dark:bg-yellow-900/20 border-yellow-200 dark:border-yellow-800 text-yellow-800 dark:text-yellow-200',
        'danger' => 'bg-red-50 dark:bg-red-900/20 border-red-200 dark:border-red-800 text-red-800 dark:text-red-200',
    ];
    
    $iconClasses = [
        'info' => 'fas fa-info-circle text-blue-600 dark:text-blue-400',
        'success' => 'fas fa-check-circle text-green-600 dark:text-green-400',
        'warning' => 'fas fa-exclamation-triangle text-yellow-600 dark:text-yellow-400',
        'danger' => 'fas fa-times-circle text-red-600 dark:text-red-400',
    ];
    
    $classes = $typeClasses[$type] ?? $typeClasses['info'];
    $icon = $iconClasses[$type] ?? $iconClasses['info'];
@endphp

<div {{ $attributes->merge(['class' => "flex items-start p-4 border rounded-lg {$classes} animate-slide-up"]) }} 
     @if($dismissible) x-data="{ show: true }" x-show="show" x-transition @endif>
    
    <!-- Icon -->
    <div class="flex-shrink-0">
        <i class="{{ $icon }} text-lg"></i>
    </div>
    
    <!-- Message -->
    <div class="ml-3 flex-1">
        <p class="text-sm font-medium">
            {{ $message }}
            {{ $slot }}
        </p>
    </div>
    
    <!-- Dismiss Button -->
    @if($dismissible)
        <button @click="show = false" class="flex-shrink-0 ml-4 inline-flex text-current hover:opacity-75 transition-opacity">
            <i class="fas fa-times text-sm"></i>
        </button>
    @endif
</div>
