<!-- Top Navbar Component -->
<nav class="sticky top-0 z-30 bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 shadow-sm transition-colors duration-300">
    <div class="px-6 py-4">
        <div class="flex items-center justify-between">
            
            <!-- Left: Page Title & Breadcrumb -->
            <div class="flex items-center space-x-4">
                <!-- Mobile Menu Toggle -->
                <button 
                    @click="window.dispatchEvent(new CustomEvent('toggle-sidebar'))"
                    class="lg:hidden p-2 rounded-lg text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    title="Toggle Sidebar"
                >
                    <i class="fas fa-bars text-lg"></i>
                </button>

                <!-- Page Title -->
                <div>
                    @php
                        // Get pageTitle - handle both slot and string
                        $rawTitle = $pageTitle ?? 'Dashboard';
                        $displayTitle = is_string($rawTitle) ? $rawTitle : (string) $rawTitle;
                        
                        // Map page titles to match sidebar menu names
                        $titleMap = [
                            'Dashboard Absensi' => 'Dashboard',
                            'Manajemen Siswa' => 'Data Siswa',
                            'Manajemen Kelas' => 'Data Kelas',
                            'Laporan Absensi' => 'Laporan',
                            'Pengaturan Sistem' => 'Settings',
                        ];
                        
                        $displayTitle = $titleMap[$displayTitle] ?? $displayTitle;
                    @endphp
                    
                    <h2 class="text-xl font-bold text-gray-800 dark:text-white">
                        {{ $displayTitle }}
                    </h2>
                    
                    <!-- Breadcrumb -->
                    @isset($breadcrumbs)
                        <nav class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400 mt-1">
                            <a href="{{ route('dashboard') }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                <i class="fas fa-home"></i>
                            </a>
                            @foreach($breadcrumbs as $breadcrumb)
                                <i class="fas fa-chevron-right text-xs"></i>
                                @if(isset($breadcrumb['url']))
                                    <a href="{{ $breadcrumb['url'] }}" class="hover:text-primary-600 dark:hover:text-primary-400 transition-colors">
                                        {{ $breadcrumb['label'] }}
                                    </a>
                                @else
                                    <span class="text-gray-700 dark:text-gray-300">{{ $breadcrumb['label'] }}</span>
                                @endif
                            @endforeach
                        </nav>
                    @endisset
                </div>
            </div>

            <!-- Right: Search, Notifications, User Menu -->
            <div class="flex items-center space-x-4">
                
                <!-- Global Search -->
                <div class="relative hidden md:block" x-data="{ searchOpen: false }">
                    <div class="relative">
                        <input 
                            type="text" 
                            placeholder="Cari siswa, kelas..." 
                            @focus="searchOpen = true"
                            @blur="setTimeout(() => searchOpen = false, 200)"
                            class="w-64 pl-10 pr-4 py-2 rounded-lg border border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-gray-700 text-gray-700 dark:text-gray-200 placeholder-gray-400 dark:placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition-all"
                        >
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    
                    <!-- Search Dropdown Results (placeholder) -->
                    <div 
                        x-show="searchOpen" 
                        x-transition
                        class="absolute top-full mt-2 w-full bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                    >
                        <div class="p-3 text-sm text-gray-500 dark:text-gray-400 text-center">
                            Ketik untuk mencari...
                        </div>
                    </div>
                </div>

                
                <!-- Dark Mode Toggle -->
                <button 
                    onclick="toggleDarkMode()"
                    id="dark-mode-toggle"
                    class="p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    title="Toggle Dark Mode"
                >
                    <i class="fas fa-moon text-lg" id="dark-icon-moon"></i>
                    <i class="fas fa-sun text-lg hidden" id="dark-icon-sun"></i>
                </button>

                <!-- Notifications -->
                <div class="relative" x-data="{ notifOpen: false }">
                    <button 
                        @click="notifOpen = !notifOpen"
                        class="relative p-2 rounded-lg text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        <i class="fas fa-bell text-lg"></i>
                        <!-- Badge -->
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span>
                    </button>

                    <!-- Notifications Dropdown -->
                    <div 
                        x-show="notifOpen" 
                        @click.away="notifOpen = false"
                        x-transition
                        class="absolute right-0 mt-2 w-80 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                    >
                        <!-- Header -->
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                            <h3 class="font-semibold text-gray-800 dark:text-white">Notifikasi</h3>
                            <span class="text-xs bg-primary-100 dark:bg-primary-900/30 text-primary-600 dark:text-primary-400 px-2 py-1 rounded-full">3 Baru</span>
                        </div>
                        
                        <!-- Notifications List -->
                        <div class="max-h-96 overflow-y-auto">
                            <!-- Sample Notification -->
                            <a href="#" class="block px-4 py-3 hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors border-b border-gray-100 dark:border-gray-700">
                                <div class="flex items-start space-x-3">
                                    <div class="flex-shrink-0 w-10 h-10 bg-primary-100 dark:bg-primary-900/30 rounded-full flex items-center justify-center">
                                        <i class="fas fa-user-check text-primary-600 dark:text-primary-400"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-800 dark:text-white">Absensi Masuk</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Ahmad Fauzi telah absen masuk</p>
                                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">5 menit yang lalu</p>
                                    </div>
                                </div>
                            </a>
                            
                            <!-- Empty State -->
                            <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                <i class="fas fa-bell-slash text-3xl mb-2 opacity-50"></i>
                                <p class="text-sm">Tidak ada notifikasi baru</p>
                            </div>
                        </div>
                        
                        <!-- Footer -->
                        <div class="px-4 py-3 bg-gray-50 dark:bg-gray-900/50 border-t border-gray-200 dark:border-gray-700">
                            <a href="#" class="text-sm text-primary-600 dark:text-primary-400 hover:text-primary-700 dark:hover:text-primary-300 font-medium">
                                Lihat semua notifikasi
                            </a>
                        </div>
                    </div>
                </div>

                <!-- User Menu Dropdown -->
                <div class="relative" x-data="{ userMenuOpen: false }">
                    <button 
                        @click="userMenuOpen = !userMenuOpen"
                        class="flex items-center space-x-3 p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors"
                    >
                        <!-- Avatar -->
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-full flex items-center justify-center text-white font-semibold shadow-lg">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        
                        <!-- User Info (hidden on mobile) -->
                        <div class="hidden md:block text-left">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-200">{{ auth()->user()->name ?? 'User' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ ucfirst(auth()->user()->role ?? 'admin') }}</p>
                        </div>
                        
                        <i class="fas fa-chevron-down text-xs text-gray-500 dark:text-gray-400 transition-transform" :class="{ 'rotate-180': userMenuOpen }"></i>
                    </button>

                    <!-- User Dropdown Menu -->
                    <div 
                        x-show="userMenuOpen" 
                        @click.away="userMenuOpen = false"
                        x-transition
                        class="absolute right-0 mt-2 w-56 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden"
                    >
                        <!-- User Info Section -->
                        <div class="px-4 py-3 border-b border-gray-200 dark:border-gray-700">
                            <p class="text-sm font-semibold text-gray-800 dark:text-white">{{ auth()->user()->name ?? 'User' }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ auth()->user()->email ?? 'user@example.com' }}</p>
                        </div>
                        
                        <!-- Menu Items -->
                        <div class="py-2">
                            <a href="{{ route('profile.edit') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <i class="fas fa-user w-5"></i>
                                <span>Profile Saya</span>
                            </a>
                            
                            <a href="{{ route('attendance.settings.index') }}" class="flex items-center space-x-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                                <i class="fas fa-cog w-5"></i>
                                <span>Settings</span>
                            </a>
                            
                            <div class="border-t border-gray-200 dark:border-gray-700 my-2"></div>
                            
                            <!-- Logout -->
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="w-full flex items-center space-x-3 px-4 py-2 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                    <i class="fas fa-sign-out-alt w-5"></i>
                                    <span>Logout</span>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</nav>
