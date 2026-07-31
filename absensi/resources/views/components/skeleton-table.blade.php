{{-- Skeleton Loader for Tables --}}
@props(['rows' => 5, 'columns' => 6])

<div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden">
    {{-- Table Header Skeleton --}}
    <div class="bg-gray-50 dark:bg-gray-900 px-6 py-3 border-b border-gray-200 dark:border-gray-700">
        <div class="flex items-center space-x-4">
            @for($i = 0; $i < $columns; $i++)
                <div class="skeleton h-4 rounded" style="width: {{ 100 / $columns }}%"></div>
            @endfor
        </div>
    </div>

    {{-- Table Body Skeleton --}}
    <div class="divide-y divide-gray-200 dark:divide-gray-700">
        @for($r = 0; $r < $rows; $r++)
        <div class="px-6 py-4">
            <div class="flex items-center space-x-4">
                @for($c = 0; $c < $columns; $c++)
                    @if($c === 0)
                        {{-- First column with image --}}
                        <div class="skeleton h-10 w-10 rounded-full"></div>
                    @else
                        <div class="skeleton h-4 rounded" style="width: {{ 100 / $columns }}%"></div>
                    @endif
                @endfor
            </div>
        </div>
        @endfor
    </div>
</div>
