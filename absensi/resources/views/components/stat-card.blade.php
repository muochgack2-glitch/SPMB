@props([
    'title' => '',
    'value' => 0,
    'icon' => 'fa-chart-line',
    'color' => 'primary',
    'trend' => null,
    'trendUp' => true
])

@php
    $colorClasses = [
        'primary' => 'from-primary-500 to-primary-600 shadow-blue-glow',
        'success' => 'from-green-500 to-green-600',
        'warning' => 'from-yellow-500 to-yellow-600',
        'danger' => 'from-red-500 to-red-600',
        'info' => 'from-blue-500 to-blue-600',
        'purple' => 'from-purple-500 to-purple-600',
    ];
    
    $gradientClass = $colorClasses[$color] ?? $colorClasses['primary'];
@endphp

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6 hover:shadow-lg transition-all duration-200 hover:-translate-y-1">
    <div class="flex items-center justify-between">
        <div class="flex-1">
            <p class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ $title }}</p>
            <p class="text-3xl font-bold text-gray-900 dark:text-white mt-2">{{ $value }}</p>
            
            @if($trend)
                <div class="flex items-center mt-2 text-sm">
                    <i class="fas {{ $trendUp ? 'fa-arrow-up text-green-500' : 'fa-arrow-down text-red-500' }} mr-1"></i>
                    <span class="{{ $trendUp ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }} font-medium">
                        {{ $trend }}
                    </span>
                    <span class="text-gray-500 dark:text-gray-400 ml-1">vs kemarin</span>
                </div>
            @endif
        </div>
        
        <div class="w-12 h-12 bg-gradient-to-br {{ $gradientClass }} rounded-lg flex items-center justify-center flex-shrink-0">
            <i class="fas {{ $icon }} text-white text-xl"></i>
        </div>
    </div>
</div>
