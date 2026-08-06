<!-- Sidebar Component with SPMB Technology (Vanilla JS + Bootstrap Tooltips + Hover Expand) -->

<!-- Mobile Overlay (SPMB Style) - BEFORE sidebar in DOM -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<aside 
    id="adminSidebar"
    class="sidebar fixed top-0 left-0 h-screen bg-gradient-to-b from-primary-900 via-primary-800 to-primary-900 shadow-2xl z-50 overflow-hidden"
    style="width: 16rem; transition: width 0.3s ease;"
>

<style>
    /* ============================================ */
    /* SPMB SIDEBAR TECHNOLOGY - CSS */
    /* ============================================ */
    
    /* Base Sidebar Styles */
    .sidebar {
        position: fixed;
        top: 0;
        left: 0;
        height: 100vh;
        background: linear-gradient(to bottom, #1e3a8a, #1e40af, #1e3a8a);
        transition: width 0.3s ease;
        overflow: hidden;
        z-index: 50;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        will-change: transform;
        backface-visibility: hidden;
    }
    
    /* Collapsed State */
    .sidebar.collapsed {
        width: 5rem !important;
    }
    
    /* ============================================ */
    /* HOVER EXPAND FEATURE (SPMB Technology) */
    /* ============================================ */
    .sidebar.collapsed:hover {
        width: 16rem !important;
    }
    
    /* Hide nav text when collapsed */
    .sidebar.collapsed .nav-text {
        opacity: 0;
        visibility: hidden;
        transition: opacity 0.2s ease, visibility 0.2s ease;
    }
    
    /* Show nav text on hover when collapsed */
    .sidebar.collapsed:hover .nav-text {
        opacity: 1;
        visibility: visible;
        transition-delay: 0.15s;
    }
    
    /* Section labels hidden when collapsed */
    .sidebar.collapsed .sidebar-section-label {
        opacity: 0;
        visibility: hidden;
    }
    
    .sidebar.collapsed:hover .sidebar-section-label {
        opacity: 1;
        visibility: visible;
        transition-delay: 0.15s;
    }
    
    /* Hide badges when collapsed (show on hover) */
    .sidebar.collapsed .sidebar-badge {
        opacity: 0;
    }
    
    .sidebar.collapsed:hover .sidebar-badge {
        opacity: 1;
        transition-delay: 0.15s;
    }
    
    /* ============================================ */
    /* SIDEBAR BRAND SECTION */
    /* ============================================ */
    .sidebar-brand {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 0.75rem;
        padding: 1.5rem 1rem;
        height: 80px;
    }
    
    .sidebar-brand-logo {
        flex-shrink: 0;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .sidebar-brand-text {
        flex: 1;
        overflow: hidden;
        transition: opacity 0.3s ease, visibility 0.3s ease;
    }
    
    /* Collapsed: center logo */
    .sidebar.collapsed .sidebar-brand {
        justify-content: center;
    }
    
    /* Hide brand text when collapsed */
    .sidebar.collapsed .sidebar-brand-text {
        opacity: 0;
        visibility: hidden;
    }
    
    .sidebar.collapsed:hover .sidebar-brand-text {
        opacity: 1;
        visibility: visible;
        transition-delay: 0.15s;
    }
    
    /* ============================================ */
    /* TOGGLE BUTTON (SPMB Position - Top Right) */
    /* ============================================ */
    .sidebar-toggle-btn {
        position: absolute;
        right: 0.75rem;
        top: 50%;
        transform: translateY(-50%);
        width: 2rem;
        height: 2rem;
        border: none;
        background: rgba(255, 255, 255, 0.1);
        color: white;
        border-radius: 50%;
        cursor: pointer;
        transition: all 0.2s ease;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 1;
        visibility: visible;
        z-index: 100;
    }
    
    .sidebar-toggle-btn:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-50%) scale(1.1);
    }
    
    .sidebar-toggle-btn:active {
        transform: translateY(-50%) scale(0.95);
    }
    
    /* Show toggle button only on desktop */
    @media (min-width: 1024px) {
        .sidebar-toggle-btn {
            display: flex !important;
        }
    }
    
    /* Hide button when collapsed (SPMB behavior) */
    .sidebar.collapsed .sidebar-toggle-btn {
        opacity: 0 !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }
    
    /* Show button when collapsed sidebar is hovered */
    .sidebar.collapsed:hover .sidebar-toggle-btn {
        opacity: 1 !important;
        visibility: visible !important;
        pointer-events: auto !important;
        right: 0.75rem !important;
        transform: translateY(-50%) !important;
    }
    
    /* ============================================ */
    /* MENU ITEMS */
    /* ============================================ */
    .sidebar-menu-item {
        position: relative;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.75rem 1rem;
        color: rgba(191, 219, 254, 0.9);
        text-decoration: none;
        border-radius: 0.5rem;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }
    
    .sidebar-menu-item:hover {
        background: rgba(30, 64, 175, 0.3);
        color: white;
        transform: translateX(4px);
    }
    
    .sidebar-menu-item.active {
        background: rgba(255, 255, 255, 0.1) !important;
        color: white;
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
    
    .sidebar-menu-item i {
        width: 1.25rem;
        text-align: center;
        flex-shrink: 0;
    }
    
    /* ============================================ */
    /* SECTION LABELS */
    /* ============================================ */
    .sidebar-section-label {
        font-size: 0.625rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: rgba(255, 255, 255, 0.4);
        padding: 0.5rem 1rem;
        margin-top: 0.5rem;
        max-height: 2rem;
        overflow: hidden;
        transition: max-height 0.3s ease, opacity 0.2s ease, margin 0.3s ease, padding 0.3s ease;
    }
    
    /* Collapsed: height to 0 */
    .sidebar.collapsed .sidebar-section-label {
        max-height: 0;
        opacity: 0;
        margin-top: 0;
        margin-bottom: 0;
        padding-top: 0;
        padding-bottom: 0;
    }
    
    /* Show on hover */
    .sidebar.collapsed:hover .sidebar-section-label {
        max-height: 2rem;
        opacity: 1;
        margin-top: 0.5rem;
        padding: 0.5rem 1rem;
        transition-delay: 0.15s;
    }
    
    /* ============================================ */
    /* BADGE NOTIFICATION */
    /* ============================================ */
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
        display: none;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.4);
        animation: pulse-badge 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        transition: opacity 0.3s ease;
    }
    
    @keyframes pulse-badge {
        0%, 100% { opacity: 1; }
        50% { opacity: .8; }
    }
    
    /* ============================================ */
    /* DIVIDER */
    /* ============================================ */
    .sidebar-divider {
        height: 1px;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
        margin: 0.75rem 0;
    }
    
    /* ============================================ */
    /* MOBILE OVERLAY (SPMB Style) */
    /* ============================================ */
    .sidebar-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040; /* Below sidebar but high enough */
    }
    
    .sidebar-overlay.show {
        display: block;
    }
    
    /* ============================================ */
    /* MOBILE MENU STATE (SPMB Style) */
    /* ============================================ */
    @media (max-width: 1023px) {
        .sidebar {
            transform: translateX(-100%);
            transition: transform 0.3s ease;
            width: 16rem !important;
            z-index: 1050 !important; /* Above overlay */
        }
        
        .sidebar.mobile-show {
            transform: translateX(0);
            z-index: 1050 !important; /* Stay on top */
        }
    }
    
    /* ============================================ */
    /* CUSTOM SCROLLBAR */
    /* ============================================ */
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
    
    /* ============================================ */
    /* BOTTOM ICON BUTTONS (COMPACT) */
    /* ============================================ */
    .bottom-section {
        padding: 0.75rem;
        border-top: 1px solid rgba(59, 130, 246, 0.5);
    }
    
    .bottom-icons-container {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-evenly;
        gap: 0.5rem;
        width: 100%;
        transition: flex-direction 0.3s ease;
    }
    
    /* Collapsed: vertical stack */
    .sidebar.collapsed .bottom-icons-container {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    /* Bottom section form - ensure it participates in flex layout properly */
    .bottom-icons-container > form {
        display: contents; /* Makes form transparent to flexbox */
    }
    
    .bottom-icon-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
        padding: 0.5rem;
        width: auto;
        border-radius: 0.5rem;
        color: rgba(191, 219, 254, 0.9);
        background: transparent;
        border: none;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        text-decoration: none;
        white-space: nowrap;
    }
    
    .bottom-icon-btn:hover {
        background: rgba(30, 64, 175, 0.3);
        color: white;
        transform: translateX(4px);
    }
    
    .bottom-icon-btn:active {
        transform: translateX(0);
    }
    
    /* Icon styling - match menu items */
    .bottom-icon-btn i,
    .bottom-icon-btn .icon-wrapper {
        width: 1.25rem;
        text-align: center;
        flex-shrink: 0;
        font-size: 1.125rem;
    }
    
    /* Avatar specific sizing - normal state */
    .bottom-icon-btn .avatar-icon {
        width: 1.5rem;
        height: 1.5rem;
        font-size: 0.75rem;
        transition: all 0.2s ease;
    }
    
    /* Avatar when collapsed - smaller to match icons */
    .sidebar.collapsed .bottom-icon-btn .avatar-icon {
        width: 1.25rem;
        height: 1.25rem;
        font-size: 0.625rem;
    }
    
    /* Avatar when collapsed + hover - back to normal */
    .sidebar.collapsed:hover .bottom-icon-btn .avatar-icon {
        width: 1.5rem;
        height: 1.5rem;
        font-size: 0.75rem;
    }
    
    /* Text labels - match menu items */
    .bottom-icon-btn .btn-text {
        display: none; /* Hide completely by default (expanded sidebar) */
        flex: 1;
        overflow: hidden;
        font-size: 1rem;
        font-weight: 500;
        line-height: 1.5;
        text-align: left;
    }
    
    /* Collapsed state: still hidden */
    .sidebar.collapsed .bottom-icon-btn {
        justify-content: center;
        padding: 0.75rem;
        width: 100%;
    }
    
    .sidebar.collapsed .bottom-icon-btn .btn-text {
        display: none;
    }
    
    /* Collapsed + hover: show text with smooth transition */
    .sidebar.collapsed:hover .bottom-icon-btn {
        justify-content: flex-start;
        padding: 0.75rem 1rem;
    }
    
    .sidebar.collapsed:hover .bottom-icon-btn .btn-text {
        display: block;
        animation: fadeInText 0.2s ease 0.15s forwards;
        opacity: 0;
    }
    
    @keyframes fadeInText {
        to {
            opacity: 1;
        }
    }
    
    /* Special styling for logout button */
    .bottom-icon-btn.logout-btn {
        color: #fca5a5;
    }
    
    .bottom-icon-btn.logout-btn:hover {
        background: rgba(220, 38, 38, 0.2);
        color: #fef2f2;
    }
</style>


    <div class="flex flex-col h-full">
        
        <!-- Logo Section with Toggle Button (SPMB Position) -->
        <div class="sidebar-brand border-b border-primary-700/50">
            <div class="sidebar-brand-logo">
                <div class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-lg flex items-center justify-center shadow-lg">
                    <i class="fas fa-qrcode text-white text-xl"></i>
                </div>
            </div>
            
            <div class="sidebar-brand-text">
                <h1 class="text-white font-bold text-lg leading-tight">Absensi QR</h1>
                <p class="text-primary-300 text-xs">SMAN 1 Jakarta</p>
            </div>
            
            <!-- Toggle Button (SPMB Position - Top Right Corner) -->
            <button class="sidebar-toggle-btn" 
                    type="button" 
                    id="sidebarToggle" 
                    title="Toggle Sidebar">
                <i class="fas fa-circle text-xs"></i>
            </button>
        </div>

        <!-- Navigation Menu -->
        <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto custom-scrollbar">
            
            <!-- Dashboard -->
            <a 
                href="{{ route('attendance.dashboard') }}"
                class="sidebar-menu-item {{ (request()->routeIs('attendance.dashboard') || request()->routeIs('dashboard')) ? 'active' : '' }}"
                data-bs-toggle="tooltip" 
                data-bs-placement="right" 
                title="Dashboard"
            >
                <i class="fas fa-home text-lg"></i>
                <span class="nav-text font-medium">Dashboard</span>
            </a>

            <!-- QR Scanner -->
            <a 
                href="{{ route('attendance.scanner') }}"
                class="sidebar-menu-item {{ request()->routeIs('attendance.scanner') ? 'active' : '' }}"
                data-bs-toggle="tooltip" 
                data-bs-placement="right" 
                title="QR Scanner"
            >
                <i class="fas fa-camera text-lg"></i>
                <span class="nav-text font-medium">QR Scanner</span>
            </a>

            <!-- Divider -->
            <div class="sidebar-divider"></div>

            <!-- Data Siswa -->
            <a 
                href="{{ route('attendance.students.index') }}"
                class="sidebar-menu-item {{ request()->routeIs('attendance.students.*') ? 'active' : '' }}"
                data-bs-toggle="tooltip" 
                data-bs-placement="right" 
                title="Data Siswa"
            >
                <i class="fas fa-users text-lg"></i>
                <span class="nav-text font-medium">Data Siswa</span>
            </a>

            <!-- Data Kelas -->
            <a 
                href="{{ route('attendance.classes.index') }}"
                class="sidebar-menu-item {{ request()->routeIs('attendance.classes.*') ? 'active' : '' }}"
                data-bs-toggle="tooltip" 
                data-bs-placement="right" 
                title="Data Kelas"
            >
                <i class="fas fa-school text-lg"></i>
                <span class="nav-text font-medium">Data Kelas</span>
            </a>


            <!-- Laporan -->
            <a 
                href="{{ route('attendance.reports.index') }}"
                class="sidebar-menu-item {{ request()->routeIs('attendance.reports.*') ? 'active' : '' }}"
                data-bs-toggle="tooltip" 
                data-bs-placement="right" 
                title="Laporan"
            >
                <i class="fas fa-chart-bar text-lg"></i>
                <span class="nav-text font-medium">Laporan</span>
                
                <!-- Badge for absent count -->
                <span class="sidebar-badge">0</span>
            </a>

            <!-- Divider -->
            <div class="sidebar-divider"></div>

            <!-- WhatsApp Gateway -->
            <a 
                href="{{ route('whatsapp.index') }}"
                class="sidebar-menu-item {{ request()->routeIs('whatsapp.*') ? 'active' : '' }}"
                data-bs-toggle="tooltip" 
                data-bs-placement="right" 
                title="WhatsApp Gateway"
            >
                <i class="fab fa-whatsapp text-lg"></i>
                <span class="nav-text font-medium">WA Gateway</span>
            </a>

            <!-- Divider -->
            <div class="sidebar-divider"></div>

            <!-- Settings -->
            <a 
                href="{{ route('attendance.settings.index') }}"
                class="sidebar-menu-item {{ request()->routeIs('attendance.settings.*') ? 'active' : '' }}"
                data-bs-toggle="tooltip" 
                data-bs-placement="right" 
                title="Settings"
            >
                <i class="fas fa-cog text-lg"></i>
                <span class="nav-text font-medium">Settings</span>
            </a>

        </nav>

        <!-- Bottom Section: Compact Icon-Only (Horizontal) -->
        <div class="bottom-section">
            <div class="bottom-icons-container">
                
                <!-- User Profile Icon -->
                <a 
                    href="{{ route('profile.edit') }}"
                    class="bottom-icon-btn"
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top" 
                    title="{{ auth()->user()->name ?? 'User' }} - Profile"
                >
                    <div class="avatar-icon bg-gradient-to-br from-primary-400 to-primary-600 rounded-full flex items-center justify-center text-white font-bold shadow-lg">
                        {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                    </div>
                    <span class="btn-text">Profile</span>
                </a>

                <!-- Dark Mode Toggle Icon -->
                <button 
                    id="darkModeToggle"
                    class="bottom-icon-btn"
                    data-bs-toggle="tooltip" 
                    data-bs-placement="top" 
                    title="Toggle Dark Mode"
                >
                    <i class="fas fa-moon text-primary-200"></i>
                    <span class="btn-text">Dark Mode</span>
                </button>

                <!-- Logout Icon -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button 
                        type="submit"
                        class="bottom-icon-btn logout-btn"
                        data-bs-toggle="tooltip" 
                        data-bs-placement="top" 
                        title="Logout"
                    >
                        <i class="fas fa-sign-out-alt"></i>
                        <span class="btn-text">Logout</span>
                    </button>
                </form>

            </div>
        </div>

    </div>
</aside>

