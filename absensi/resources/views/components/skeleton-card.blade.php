{{-- Skeleton Loader for Stat Cards --}}
@props(['type' => 'stat'])

@if($type === 'stat')
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <div class="skeleton h-4 w-24 rounded mb-3"></div>
                <div class="skeleton h-8 w-16 rounded mb-2"></div>
                <div class="skeleton h-3 w-20 rounded"></div>
            </div>
            <div class="skeleton h-12 w-12 rounded-full"></div>
        </div>
    </div>
@elseif($type === 'chart')
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <div class="skeleton h-5 w-32 rounded mb-4"></div>
        <div class="space-y-3">
            <div class="skeleton h-48 w-full rounded"></div>
        </div>
    </div>
@elseif($type === 'activity')
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <div class="skeleton h-5 w-32 rounded mb-4"></div>
        <div class="space-y-4">
            @for($i = 0; $i < 5; $i++)
            <div class="flex items-center space-x-3">
                <div class="skeleton h-10 w-10 rounded-full"></div>
                <div class="flex-1">
                    <div class="skeleton h-4 w-32 rounded mb-2"></div>
                    <div class="skeleton h-3 w-24 rounded"></div>
                </div>
                <div class="skeleton h-6 w-16 rounded-full"></div>
            </div>
            @endfor
        </div>
    </div>
@else
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
        <div class="skeleton h-5 w-40 rounded mb-4"></div>
        <div class="space-y-3">
            <div class="skeleton h-4 w-full rounded"></div>
            <div class="skeleton h-4 w-full rounded"></div>
            <div class="skeleton h-4 w-3/4 rounded"></div>
        </div>
    </div>
@endif
