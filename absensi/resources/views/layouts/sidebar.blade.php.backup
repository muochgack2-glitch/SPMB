<!-- Sidebar Component with Enhanced UI/UX & Mobile Responsive -->
<aside 
    data-sidebar
    id="main-sidebar"
    x-data="sidebarData()"
    x-init="initSidebar()"
    style="pointer-events: auto !important;"
    class="fixed top-0 left-0 h-screen bg-gradient-to-b from-primary-900 via-primary-800 to-primary-900 shadow-2xl z-50 overflow-hidden"
    :class="[
        isInitialLoad ? '' : 'transition-all duration-300',
        isMobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'
    ]"
    :style="{ width: sidebarOpen ? '16rem' : '5rem' }"
>
<style>
    /* Mobile Overlay */
    .mobile-overlay {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        backdrop-filter: blur(4px);
        z-index: 40;
        transition: opacity 0.3s ease;
    }
    
    /* Hamburger Menu Button */
    .hamburger-button {
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 60;
        width: 3rem;
        height: 3rem;
        background: linear-gradient(135deg, #1e40af, #3b82f6);
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .hamburger-button:hover {
        transform: scale(1.05);
        box-shadow: 0 6px 16px rgba(0, 0, 0, 0.2);
    }
    
    .hamburger-button:active {
        transform: scale(0.95);
    }
    
    /* Show hamburger only on mobile/tablet */
    @media (min-width: 1024px) {
        .hamburger-button {
            display: none;
        }
    }
    
    /* Responsive sidebar */
    @media (max-width: 1023px) {
        #main-sidebar {
            width: 16rem !important; /* Force full width on mobile */
        }
    }

    /* Enhanced Menu Item Styles */
    .sidebar-menu-item {
        position: relative;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .sidebar-menu-item:hover {
        transform: translateX(4px);
    }
    
    .sidebar-menu-item.active {
        background: rgba(255, 255, 255, 0.1) !important;
        border-left: 4px solid white;
        padding-left: calc(1rem - 4px);
    }
    
    .sidebar-menu-item.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 4px;
        background: linear-gradient(180deg, rgba(255,255,255,0.9), rgba(255,255,255,0.5));
        box-shadow: 0 0 10px rgba(255,255,255,0.5);
    }
    
    /* Badge Notification */
    .sidebar-badge {
        position: absolute;
        top: 0.5rem;
        right: 0.5rem;
        min-width: 1.25rem;
        height: 1.25rem;
        padding: 0 0.375rem;
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
        font-size: 0.625rem;
        font-weight: 700;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
        animation: pulse-badge 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    
    @keyframes pulse-badge {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: .8;
        }
    }
    
    /* Divider Style */
    .sidebar-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        margin: 0.75rem 0;
    }
    
    .sidebar-section-label {
        font-size: 0.625rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: rgba(255, 255, 255, 0.4);
        padding: 0.5rem 1rem;
        margin-top: 0.5rem;
    }
    
    /* Custom scrollbar */
    .custom-scrollbar::-webkit-scrollbar {
        width: 4px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-track {
        background: rgba(255, 255, 255, 0.05);
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: rgba(255, 255, 255, 0.2);
        border-radius: 2px;
    }
    
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: rgba(255, 255, 255, 0.3);
    }
</style>
<script>
function sidebarData() {
    return {
        sidebarOpen: localStorage.getItem('sidebarOpen') !== 'false',
        activeMenu: '{{ request()->route()->getName() }}',
        tooltipShow: null,
        isInitialLoad: true,
        todayAbsentCount: 0,
        isMobileMenuOpen: false,
        
        toggleSidebar() {
            this.isInitialLoad = false;
            this.sidebarOpen = !this.sidebarOpen;
            localStorage.setItem('sidebarOpen', this.sidebarOpen);
            window.dispatchEvent(new CustomEvent('sidebar-toggled', { detail: this.sidebarOpen }));
        },
        
        toggleMobileMenu() {
            this.isMobileMenuOpen = !this.isMobileMenuOpen;
        },
        
        closeMobileMenu() {
            this.isMobileMenuOpen = false;
        },
        
        initSidebar() {
            // Remove transition during initial load
            setTimeout(() => { this.isInitialLoad = false; }, 100);
            
            // Listen for external toggle events
            window.addEventListener('toggle-sidebar', () => {
                this.toggleSidebar();
            });
            
            // Listen for mobile menu toggle
            window.addEventListener('toggle-mobile-menu', () => {
                this.toggleMobileMenu();
            });
            
            // Close mobile menu on window resize to desktop
            window.addEventListener('resize', () => {
                if (window.innerWidth >= 1024) {
                    this.isMobileMenuOpen = false;
                }
            });
            
            // Load badge counts
            this.loadBadgeCounts();
        },
        
        loadBadgeCounts() {
            // Fetch today's absent count for badge
            fetch('/api/attendance/today-stats')
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.todayAbsentCount = data.absent || 0;
                    }
                })
                .catch(err => console.log('Badge count fetch error:', err));
        }
    }
}
</script>

    <!-- Mobile Overlay (when menu is open) -->
    <div 
        x-show="isMobileMenuOpen" 
        @click="closeMobileMenu()"
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="mobile-overlay lg:hidden"
    ></div>

    <!-- Hamburger Menu Button (Mobile/Tablet only) -->
    <button 
        @click="toggleMobileMenu()"
        class="hamburger-button lg:hidden"
        aria-label="Toggle menu"
    >
        <i class="fas text-white text-lg" :class="isMobileMenuOpen ? 'fa-times' : 'fa-bars'"></i>
    </button>

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

        <!-- Navigation Menu with Enhanced UX -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar" style="pointer-events: auto !important;">
            
            <!-- MAIN MENU SECTION -->
            <div x-show="sidebarOpen" x-transition class="sidebar-section-label">
                📊 Main Menu
            </div>
            
            <!-- Dashboard -->
            <a 
                href="{{ route('attendance.dashboard') }}"
                @click="closeMobileMenu()"
                @mouseenter="!sidebarOpen && (tooltipShow = 'dashboard')"
                @mouseleave="tooltipShow = null"
                class="sidebar-menu-item relative flex items-center space-x-3 px-4 py-3 rounded-lg group"
                :class="(activeMenu === 'attendance.dashboard' || activeMenu === 'dashboard') ? 'active' : 'text-primary-200 hover:bg-primary-800/30 hover:text-white'"
            >
                <i class="fas fa-home text-lg w-5 text-center flex-shrink-0" :class="(activeMenu === 'attendance.dashboard' || activeMenu === 'dashboard') ? 'text-white' : ''"></i>
                <span x-show="sidebarOpen" x-transition class="font-medium" :class="(activeMenu === 'attendance.dashboard' || activeMenu === 'dashboard') ? 'text-white' : ''">Dashboard</span>
                
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
                @click="closeMobileMenu()"
                @mouseenter="!sidebarOpen && (tooltipShow = 'scan')"
                @mouseleave="tooltipShow = null"
                class="sidebar-menu-item relative flex items-center space-x-3 px-4 py-3 rounded-lg group"
                :class="activeMenu === 'attendance.scanner' ? 'active' : 'text-primary-200 hover:bg-primary-800/30 hover:text-white'"
            >
                <i class="fas fa-camera text-lg w-5 text-center flex-shrink-0" :class="activeMenu === 'attendance.scanner' ? 'text-white' : ''"></i>
                <span x-show="sidebarOpen" x-transition class="font-medium" :class="activeMenu === 'attendance.scanner' ? 'text-white' : ''">QR Scanner</span>
                
                <div x-show="!sidebarOpen && tooltipShow === 'scan'" 
                     class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg shadow-lg whitespace-nowrap z-50"
                     x-transition>
                    QR Scanner
                </div>
            </a>

            <!-- Divider -->
            <div class="sidebar-divider"></div>

            <!-- DATA MANAGEMENT SECTION -->
            <div x-show="sidebarOpen" x-transition class="sidebar-section-label">
                📁 Data Management
            </div>

            <!-- Data Siswa -->
            <a 
                href="{{ route('attendance.students.index') }}"
                @click="closeMobileMenu()"
                @mouseenter="!sidebarOpen && (tooltipShow = 'students')"
                @mouseleave="tooltipShow = null"
                class="sidebar-menu-item relative flex items-center space-x-3 px-4 py-3 rounded-lg group"
                :class="(activeMenu === 'attendance.students.index' || activeMenu === 'attendance.students.create' || activeMenu === 'attendance.students.edit') ? 'active' : 'text-primary-200 hover:bg-primary-800/30 hover:text-white'"
            >
                <i class="fas fa-users text-lg w-5 text-center flex-shrink-0" :class="activeMenu.includes('students') ? 'text-white' : ''"></i>
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
                @click="closeMobileMenu()"
                @mouseenter="!sidebarOpen && (tooltipShow = 'classes')"
                @mouseleave="tooltipShow = null"
                class="sidebar-menu-item relative flex items-center space-x-3 px-4 py-3 rounded-lg group"
                :class="(activeMenu === 'attendance.classes.index' || activeMenu === 'attendance.classes.create' || activeMenu === 'attendance.classes.edit') ? 'active' : 'text-primary-200 hover:bg-primary-800/30 hover:text-white'"
            >
                <i class="fas fa-school text-lg w-5 text-center flex-shrink-0" :class="activeMenu.includes('classes') ? 'text-white' : ''"></i>
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
                @click="closeMobileMenu()"
                @mouseenter="!sidebarOpen && (tooltipShow = 'reports')"
                @mouseleave="tooltipShow = null"
                class="sidebar-menu-item relative flex items-center space-x-3 px-4 py-3 rounded-lg group"
                :class="activeMenu === 'attendance.reports.index' ? 'active' : 'text-primary-200 hover:bg-primary-800/30 hover:text-white'"
            >
                <i class="fas fa-chart-bar text-lg w-5 text-center flex-shrink-0" :class="activeMenu === 'attendance.reports.index' ? 'text-white' : ''"></i>
                <span x-show="sidebarOpen" x-transition class="font-medium" :class="activeMenu === 'attendance.reports.index' ? 'text-white' : ''">Laporan</span>
                
                <!-- Badge for absent count -->
                <span x-show="sidebarOpen && todayAbsentCount > 0" x-text="todayAbsentCount" class="sidebar-badge"></span>
                
                <div x-show="!sidebarOpen && tooltipShow === 'reports'" 
                     class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg shadow-lg whitespace-nowrap z-50"
                     x-transition>
                    Laporan
                </div>
            </a>

            <!-- Divider -->
            <div class="sidebar-divider"></div>

            <!-- INTEGRATION SECTION -->
            <div x-show="sidebarOpen" x-transition class="sidebar-section-label">
                📱 Integration
            </div>

            <!-- WhatsApp Gateway -->
            <a 
                href="{{ route('whatsapp.index') }}"
                @click="closeMobileMenu()"
                @mouseenter="!sidebarOpen && (tooltipShow = 'whatsapp')"
                @mouseleave="tooltipShow = null"
                class="sidebar-menu-item relative flex items-center space-x-3 px-4 py-3 rounded-lg group"
                :class="(activeMenu === 'whatsapp.index' || activeMenu.includes('whatsapp.')) ? 'active' : 'text-primary-200 hover:bg-primary-800/30 hover:text-white'"
            >
                <i class="fab fa-whatsapp text-lg w-5 text-center flex-shrink-0" :class="activeMenu.includes('whatsapp.') ? 'text-white' : ''"></i>
                <span x-show="sidebarOpen" x-transition class="font-medium" :class="activeMenu.includes('whatsapp.') ? 'text-white' : ''">WA Gateway</span>
                
                <div x-show="!sidebarOpen && tooltipShow === 'whatsapp'" 
                     class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg shadow-lg whitespace-nowrap z-50"
                     x-transition>
                    WhatsApp Gateway
                </div>
            </a>

            <!-- Divider -->
            <div class="sidebar-divider"></div>

            <!-- SYSTEM SECTION -->
            <div x-show="sidebarOpen" x-transition class="sidebar-section-label">
                ⚙️ System
            </div>

            <!-- Settings -->
            <a 
                href="{{ route('attendance.settings.index') }}"
                @click="closeMobileMenu()"
                @mouseenter="!sidebarOpen && (tooltipShow = 'settings')"
                @mouseleave="tooltipShow = null"
                class="sidebar-menu-item relative flex items-center space-x-3 px-4 py-3 rounded-lg group"
                :class="activeMenu === 'attendance.settings.index' ? 'active' : 'text-primary-200 hover:bg-primary-800/30 hover:text-white'"
            >
                <i class="fas fa-cog text-lg w-5 text-center flex-shrink-0" :class="activeMenu === 'attendance.settings.index' ? 'text-white' : ''"></i>
                <span x-show="sidebarOpen" x-transition class="font-medium" :class="activeMenu === 'attendance.settings.index' ? 'text-white' : ''">Settings</span>
                
                <div x-show="!sidebarOpen && tooltipShow === 'settings'" 
                     class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg shadow-lg whitespace-nowrap z-50"
                     x-transition>
                    Settings
                </div>
            </a>

        </nav>

        <!-- User Profile Section -->
        <div class="px-3 py-4 border-t border-primary-700/50" x-data="{ userMenuOpen: false }">
            <div class="relative">
                <button 
                    @click="userMenuOpen = !userMenuOpen"
                    @mouseenter="!sidebarOpen && (tooltipShow = 'profile')"
                    @mouseleave="tooltipShow = null"
                    class="relative w-full flex items-center space-x-3 px-4 py-3 rounded-lg text-primary-200 hover:bg-primary-800/50 hover:text-white transition-all duration-200"
                >
                    <div class="w-8 h-8 bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center text-white font-semibold shadow-lg flex-shrink-0">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <div x-show="sidebarOpen" x-transition class="flex-1 text-left overflow-hidden">
                        <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'User' }}</p>
                        <p class="text-xs text-primary-300 truncate">{{ ucfirst(auth()->user()->role ?? 'admin') }}</p>
                    </div>
                    <i x-show="sidebarOpen" class="fas fa-chevron-down text-xs transition-transform" :class="{ 'rotate-180': userMenuOpen }"></i>
                    
                    <div x-show="!sidebarOpen && tooltipShow === 'profile'" 
                         class="absolute left-full ml-2 px-3 py-1.5 bg-gray-900 text-white text-sm rounded-lg shadow-lg whitespace-nowrap z-50"
                         x-transition>
                        {{ auth()->user()->name ?? 'User' }}
                    </div>
                </button>
                
                <!-- User Dropdown Menu -->
                <div 
                    x-show="userMenuOpen && sidebarOpen" 
                    @click.away="userMenuOpen = false"
                    x-transition
                    class="mt-2 space-y-1"
                >
                    <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 px-4 py-2 rounded-lg text-primary-200 hover:bg-primary-800/50 hover:text-white transition-all text-sm">
                        <i class="fas fa-user-cog w-5"></i>
                        <span>Profile Saya</span>
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center space-x-3 px-4 py-2 rounded-lg text-red-300 hover:bg-red-900/30 hover:text-red-200 transition-all text-sm">
                            <i class="fas fa-sign-out-alt w-5"></i>
                            <span>Logout</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>

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
                    // Sync with store if available after Alpine fully initializes
                    $nextTick(() => {
                        if ($store.darkMode) {
                            $watch('$store.darkMode.isDark', value => isDark = value);
                        }
                    });
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
