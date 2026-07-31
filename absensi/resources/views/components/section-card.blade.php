@props([
    'title' => '',
    'subtitle' => '',
    'actions' => null
])

<div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 overflow-hidden">
    @if($title || $actions)
        <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900/50">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $title }}</h3>
                    @if($subtitle)
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $subtitle }}</p>
                    @endif
                </div>
                
                @if($actions)
                    <div class="flex items-center space-x-2">
                        {{ $actions }}
                    </div>
                @endif
            </div>
        </div>
    @endif
    
    <div class="p-6">
        {{ $slot }}
    </div>
</div>
