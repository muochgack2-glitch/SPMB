{{-- Toast Notification Container --}}
<div 
    x-data="toastContainer()" 
    class="fixed top-4 right-4 z-50 space-y-3 max-w-sm w-full pointer-events-none"
    role="region"
    aria-live="polite"
    aria-label="Notifications"
>
    <template x-for="toast in toasts" :key="toast.id">
        <div 
            x-show="toast.show"
            x-transition:enter="transition ease-out duration-300 transform"
            x-transition:enter-start="translate-x-full opacity-0"
            x-transition:enter-end="translate-x-0 opacity-100"
            x-transition:leave="transition ease-in duration-300 transform"
            x-transition:leave-start="translate-x-0 opacity-100"
            x-transition:leave-end="translate-x-full opacity-0"
            class="pointer-events-auto relative rounded-lg shadow-lg overflow-hidden"
            :class="getColorClasses(toast.type)"
        >
            {{-- Toast Content --}}
            <div class="p-4 pr-12">
                <div class="flex items-start">
                    {{-- Icon --}}
                    <div class="flex-shrink-0">
                        <i class="fas text-white text-xl" :class="getIcon(toast.type)"></i>
                    </div>

                    {{-- Message --}}
                    <div class="ml-3 flex-1">
                        <h3 class="text-sm font-semibold text-white" x-text="toast.title"></h3>
                        <p class="mt-1 text-sm text-white opacity-90" x-text="toast.message"></p>
                    </div>
                </div>
            </div>

            {{-- Close Button --}}
            <button 
                @click="remove(toast.id)"
                class="absolute top-4 right-4 text-white hover:text-gray-200 transition-colors focus:outline-none focus:ring-2 focus:ring-white focus:ring-opacity-50 rounded"
                aria-label="Tutup notifikasi"
            >
                <i class="fas fa-times"></i>
            </button>

            {{-- Progress Bar --}}
            <div class="absolute bottom-0 left-0 h-1 bg-white bg-opacity-30">
                <div 
                    class="h-full bg-white transition-all duration-50 ease-linear"
                    :style="`width: ${toast.progress}%`"
                ></div>
            </div>
        </div>
    </template>
</div>
