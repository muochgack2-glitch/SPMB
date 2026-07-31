<!-- Sidebar Component -->
<aside 
    data-sidebar
    id="main-sidebar"
    x-data="sidebarData()"
    x-init="initSidebar()"
    style="pointer-events: auto !important;"
    class="fixed top-0 left-0 h-screen bg-gradient-to-b from-primary-900 via-primary-800 to-primary-900 shadow-2xl z-50 overflow-hidden"
    :class="isInitialLoad ? '' : 'transition-all duration-300'"
    :style="{ width: sidebarOpen ? '16rem' : '5rem' }"
>
<script>
function sidebarData() {
    return {
        sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
        activeMenu: '{{ request()->route()->getName() }}',
        tooltipShow: null,
        isInitialLoad: true,
        
        toggleSidebar() {
            this.isInitialLoad = false;
            this.sidebarOpen = !this.sidebarOpen;
            localStorage.setItem('sidebarOpen', this.sidebarOpen);
            window.dispatchEvent(new CustomEvent('sidebar-toggled', { detail: this.sidebarOpen }));
        },
        
        initSidebar() {
            // Remove transition during initial load
            setTimeout(() => { this.isInitialLoad = false; }, 100);
            
            // Listen for external toggle events from hamburger button
            window.addEventListener('toggle-sidebar', () => {
                this.toggleSidebar();
            });
        }
    }
}
</script>
    <div class="flex flex-col h-full">
        
        <!-- Logo Section -->
        <div class="flex items-center justify-between px-4 py-6 border-b border-primary-700/50">
            <div class="flex items-center space-x-3 overflow-hidden">
                <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center shadow-blue-glow">
                    <i class="fas fa-qrcode text-white text-xl"></i>
                </div>
                <div x-show="sidebarOpen" x-transition class="overflow-hidden">
                    <h1 class="text-white font-bold text-lg leading-tight">Absensi QR</h1>
                    <p class="text-primary-300 text-xs">SMAN 1 Jakarta</p>
                </div>
            </div>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-3 py-6 space-y-2 overflow-y-auto custom-scrollbar" style="pointer-events: auto !important;">
            
            <!-- Dashboard -->
            <a 
                href="{{ route('attendance.dashboard') }}"
                @mouseenter="!sidebarOpen && (tooltipShow = 'dashboard')"
                @mouseleave="tooltipShow = null"
                class="relative flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                :class="activeMenu === 'attendance.dashboard' || activeMenu === 'dashboard' ? 'bg-gradient-to-r from-primary-500 to-primary-600 shadow-lg shadow-primary-500/50' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white'"
            >
                <i class="fas fa-home text-lg w-5 text-center" :class="activeMenu === 'attendance.dashboard' || activeMenu === 'dashboard' ? 'text-white' : ''"></i>
                <span x-show="sidebarOpen" x-transition class="font-medium" :class="activeMenu === 'attendance.dashboard' || activeMenu === 'dashboard' ? 'text-white' : ''">Dashboard</span>
                
                <!-- Tooltip for collapsed state -->
                <div x-show="!sidebarOpen && tooltipShow === 'dashboard'" 
                     class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg shadow-lg whitespace-nowrap z-50"
                     x-transition>
                    Dashboard
                </div>
            </a>

            <!-- QR Scanner -->
            <a 
                href="{{ route('attendance.scanner') }}"
                @mouseenter="!sidebarOpen && (tooltipShow = 'scan')"
                @mouseleave="tooltipShow = null"
                class="relative flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                :class="activeMenu === 'attendance.scanner' ? 'bg-gradient-to-r from-primary-500 to-primary-600 shadow-lg shadow-primary-500/50' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white'"
            >
                <i class="fas fa-camera text-lg w-5 text-center" :class="activeMenu === 'attendance.scanner' ? 'text-white' : ''"></i>
                <span x-show="sidebarOpen" x-transition class="font-medium" :class="activeMenu === 'attendance.scanner' ? 'text-white' : ''">QR Scanner</span>
                
                <div x-show="!sidebarOpen && tooltipShow === 'scan'" 
                     class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg shadow-lg whitespace-nowrap z-50"
                     x-transition>
                    QR Scanner
                </div>
            </a>

            <!-- Data Siswa -->
            <a 
                href="{{ route('attendance.students.index') }}"
                @mouseenter="!sidebarOpen && (tooltipShow = 'students')"
                @mouseleave="tooltipShow = null"
                class="relative flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                :class="activeMenu === 'attendance.students.index' || activeMenu === 'attendance.students.create' || activeMenu === 'attendance.students.edit' ? 'bg-gradient-to-r from-primary-500 to-primary-600 shadow-lg shadow-primary-500/50' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white'"
            >
                <i class="fas fa-users text-lg w-5 text-center" :class="activeMenu.includes('students') ? 'text-white' : ''"></i>
                <span x-show="sidebarOpen" x-transition class="font-medium" :class="activeMenu.includes('students') ? 'text-white' : ''">Data Siswa</span>
                
                <div x-show="!sidebarOpen && tooltipShow === 'students'" 
                     class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg shadow-lg whitespace-nowrap z-50"
                     x-transition>
                    Data Siswa
                </div>
            </a>

            <!-- Data Kelas -->
            <a 
                href="{{ route('attendance.classes.index') }}"
                @mouseenter="!sidebarOpen && (tooltipShow = 'classes')"
                @mouseleave="tooltipShow = null"
                class="relative flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                :class="activeMenu === 'attendance.classes.index' || activeMenu === 'attendance.classes.create' || activeMenu === 'attendance.classes.edit' ? 'bg-gradient-to-r from-primary-500 to-primary-600 shadow-lg shadow-primary-500/50' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white'"
            >
                <i class="fas fa-school text-lg w-5 text-center" :class="activeMenu.includes('classes') ? 'text-white' : ''"></i>
                <span x-show="sidebarOpen" x-transition class="font-medium" :class="activeMenu.includes('classes') ? 'text-white' : ''">Data Kelas</span>
                
                <div x-show="!sidebarOpen && tooltipShow === 'classes'" 
                     class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg shadow-lg whitespace-nowrap z-50"
                     x-transition>
                    Data Kelas
                </div>
            </a>

            <!-- Laporan -->
            <a 
                href="{{ route('attendance.reports.index') }}"
                @mouseenter="!sidebarOpen && (tooltipShow = 'reports')"
                @mouseleave="tooltipShow = null"
                class="relative flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                :class="activeMenu === 'attendance.reports.index' ? 'bg-gradient-to-r from-primary-500 to-primary-600 shadow-lg shadow-primary-500/50' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white'"
            >
                <i class="fas fa-chart-bar text-lg w-5 text-center" :class="activeMenu === 'attendance.reports.index' ? 'text-white' : ''"></i>
                <span x-show="sidebarOpen" x-transition class="font-medium" :class="activeMenu === 'attendance.reports.index' ? 'text-white' : ''">Laporan</span>
                
                <div x-show="!sidebarOpen && tooltipShow === 'reports'" 
                     class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg shadow-lg whitespace-nowrap z-50"
                     x-transition>
                    Laporan
                </div>
            </a>

            <!-- Settings -->
            <a 
                href="{{ route('attendance.settings.index') }}"
                @mouseenter="!sidebarOpen && (tooltipShow = 'settings')"
                @mouseleave="tooltipShow = null"
                class="relative flex items-center space-x-3 px-4 py-3 rounded-lg transition-all duration-200 group"
                :class="activeMenu === 'attendance.settings.index' ? 'bg-gradient-to-r from-primary-500 to-primary-600 shadow-lg shadow-primary-500/50' : 'text-primary-200 hover:bg-primary-800/50 hover:text-white'"
            >
                <i class="fas fa-cog text-lg w-5 text-center" :class="activeMenu === 'attendance.settings.index' ? 'text-white' : ''"></i>
                <span x-show="sidebarOpen" x-transition class="font-medium" :class="activeMenu === 'attendance.settings.index' ? 'text-white' : ''">Settings</span>
                
                <div x-show="!sidebarOpen && tooltipShow === 'settings'" 
                     class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg shadow-lg whitespace-nowrap z-50"
                     x-transition>
                    Settings
                </div>
            </a>

        </nav>

        <!-- Bottom Section: Dark Mode & Collapse Toggle -->
        <div class="px-3 py-4 border-t border-primary-700/50 space-y-2">
            
            <!-- Dark Mode Toggle -->
            <button 
                x-data="{ 
                    isDark: localStorage.getItem('darkMode') === 'true',
                    toggleDarkMode() {
                        // Try using store first
                        if (window.Alpine && window.Alpine.store('darkMode')) {
                            window.Alpine.store('darkMode').toggle();
                            this.isDark = window.Alpine.store('darkMode').isDark;
                        } else {
                            // Fallback: manual toggle
                            this.isDark = !this.isDark;
                            localStorage.setItem('darkMode', this.isDark);
                            if (this.isDark) {
                                document.documentElement.classList.add('dark');
                            } else {
                                document.documentElement.classList.remove('dark');
                            }
                        }
                        console.log('Dark mode toggled to:', this.isDark);
                    }
                }"
                x-init="
                    // Sync with store if available
                    $watch('$store.darkMode.isDark', value => isDark = value);
                "
                @click="toggleDarkMode()"
                @mouseenter="!sidebarOpen && (tooltipShow = 'darkmode')"
                @mouseleave="tooltipShow = null"
                class="relative w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-primary-200 hover:bg-primary-800/50 hover:text-white transition-all duration-200"
            >
                <i class="text-lg w-5 text-center" :class="isDark ? 'fas fa-sun' : 'fas fa-moon'"></i>
                <span x-show="sidebarOpen" x-transition class="font-medium">
                    <span x-show="isDark">Light Mode</span>
                    <span x-show="!isDark">Dark Mode</span>
                </span>
                
                <div x-show="!sidebarOpen && tooltipShow === 'darkmode'" 
                     class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg shadow-lg whitespace-nowrap z-50"
                     x-transition>
                    <span x-show="isDark">Light Mode</span>
                    <span x-show="!isDark">Dark Mode</span>
                </div>
            </button>

            <!-- Collapse/Expand Toggle -->
            <button 
                @click="toggleSidebar()"
                @mouseenter="!sidebarOpen && (tooltipShow = 'toggle')"
                @mouseleave="tooltipShow = null"
                class="relative w-full flex items-center justify-center px-4 py-3 rounded-lg text-primary-200 hover:bg-primary-800/50 hover:text-white transition-all duration-200"
            >
                <i class="fas text-lg transition-transform duration-300" :class="sidebarOpen ? 'fa-angles-left' : 'fa-angles-right'"></i>
                
                <div x-show="!sidebarOpen && tooltipShow === 'toggle'" 
                     class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg shadow-lg whitespace-nowrap z-50"
                     x-transition>
                    Perbesar
                </div>
            </button>
        </div>

    </div>
</aside>
