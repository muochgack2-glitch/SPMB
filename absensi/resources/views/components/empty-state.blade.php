@props([
    'icon' => 'fa-inbox',
    'title' => 'Tidak ada data',
    'message' => 'Belum ada data yang tersedia saat ini.',
    'action' => null,
    'actionText' => null
])

<div class="flex flex-col items-center justify-center py-12 px-4 text-center">
    <div class="w-16 h-16 bg-gray-100 dark:bg-gray-800 rounded-full flex items-center justify-center mb-4">
        <i class="fas {{ $icon }} text-3xl text-gray-400 dark:text-gray-600"></i>
    </div>
    
    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ $title }}</h3>
    <p class="text-sm text-gray-500 dark:text-gray-400 max-w-md mb-6">{{ $message }}</p>
    
    @if($action && $actionText)
        <a href="{{ $action }}" class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-primary-500 to-primary-600 text-white text-sm font-medium rounded-lg hover:from-primary-600 hover:to-primary-700 transition-all duration-200 hover:-translate-y-0.5 shadow-md hover:shadow-lg">
            {{ $actionText }}
        </a>
    @endif
    
    {{ $slot }}
</div>
