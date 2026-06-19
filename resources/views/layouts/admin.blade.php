<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin SPMB')</title>
    @include('partials.favicon')
    @include('partials.admin-theme')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="{{ asset('css/modern-utilities.css') }}" rel="stylesheet">
    @stack('styles')
    @include('partials.admin-theme-vars')
    
    <!-- Prevent Sidebar Flash - Load State Before Render -->
    <script>
        // Load sidebar state immediately before page renders
        (function() {
            const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
            if (sidebarCollapsed && window.innerWidth >= 992) {
                document.documentElement.classList.add('sidebar-collapsed');
            }
        })();
    </script>
    
    <style>
        html {
            overflow-y: scroll;
        }

        body {
            background-color: #f5f7fa !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            margin: 0;
            padding: 0;
        }

        /* Modern Layout - eRapor8 Style */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 250px !important;
            background: #ffffff !important;
            min-height: 100vh !important;
            padding: 0 !important;
            flex: 0 0 250px !important;
            transition: none !important; /* Disable transition on page load */
            overflow-y: auto !important;
            overflow-x: hidden !important;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 1000;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05) !important;
            border-right: 1px solid #e2e8f0 !important;
        }
        
        /* Enable transitions after page load */
        .sidebar.transitions-enabled {
            transition: all 0.3s ease !important;
        }

        .sidebar.collapsed {
            width: 70px !important;
            flex: 0 0 70px !important;
        }
        
        /* Pre-collapsed state from localStorage (before JS runs) */
        html.sidebar-collapsed .sidebar {
            width: 70px !important;
            flex: 0 0 70px !important;
        }
        
        html.sidebar-collapsed .sidebar .nav-text {
            opacity: 0 !important;
            width: 0 !important;
            display: inline-block !important;
            overflow: hidden !important;
        }
        
        html.sidebar-collapsed .sidebar .nav-link {
            justify-content: center !important;
            padding: 12px 10px !important;
        }
        
        html.sidebar-collapsed .sidebar .sidebar-brand {
            padding: 20px 10px;
            justify-content: center;
        }
        
        html.sidebar-collapsed .sidebar .sidebar-brand-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        html.sidebar-collapsed .main-wrapper {
            margin-left: 70px !important;
        }

        .sidebar.collapsed .nav-text {
            opacity: 0 !important;
            width: 0 !important;
            display: inline-block !important;
            overflow: hidden !important;
        }

        .sidebar.collapsed .nav-link {
            justify-content: center !important;
            padding: 12px 10px !important;
        }

        /* Sidebar Brand */
        .sidebar-brand {
            padding: 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 12px;
            min-height: 80px;
            transition: all 0.3s ease;
            position: relative;
            background: #ffffff;
        }

        .sidebar.collapsed .sidebar-brand {
            padding: 20px 10px;
            justify-content: center;
        }
        
        /* Sidebar Toggle Button in Sidebar */
        .sidebar-toggle-btn {
            position: absolute;
            right: 20px;
            top: 30px;
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: transparent;
            border: 2px solid #cbd5e1;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background 0.3s ease, border-color 0.3s ease, color 0.3s ease, opacity 0.3s ease;
            z-index: 10;
            font-size: 10px;
            padding: 0;
            opacity: 1;
        }
        
        .sidebar-toggle-btn:hover {
            background: #f1f5f9;
            border-color: var(--primary);
            color: var(--primary);
        }
        
        /* When collapsed, hide the button */
        .sidebar.collapsed .sidebar-toggle-btn {
            opacity: 0;
            pointer-events: none;
        }
        
        /* Show button when collapsed sidebar is hovered */
        .sidebar.collapsed:hover .sidebar-toggle-btn {
            opacity: 1;
            pointer-events: auto;
            right: 20px;
        }
        
        /* Hide toggle button on mobile */
        @media (max-width: 991px) {
            .sidebar-toggle-btn {
                display: none !important;
            }
        }

        .sidebar-brand-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 20px;
            flex-shrink: 0;
        }
        
        .sidebar-brand-logo {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            overflow: hidden;
            border: 1px solid #e2e8f0;
        }
        
        .sidebar-brand-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 4px;
        }

        .sidebar-brand-text {
            color: #1e293b;
            font-weight: 700;
            font-size: 24px;
            line-height: 1.2;
            transition: opacity 0.3s ease;
        }

        .sidebar.collapsed .sidebar-brand-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        
        .sidebar.collapsed .sidebar-brand-logo,
        .sidebar.collapsed .sidebar-brand-icon {
            display: flex;
        }
        
        /* Hover Expanded State (when collapsed sidebar is hovered) */
        .sidebar.collapsed:hover {
            width: 250px !important;
            flex: 0 0 250px !important;
            z-index: 1001 !important;
            box-shadow: 4px 0 12px rgba(0, 0, 0, 0.15) !important;
        }
        
        .sidebar.collapsed:hover .nav-text {
            opacity: 1 !important;
            width: auto !important;
        }
        
        .sidebar.collapsed:hover .nav-link {
            justify-content: flex-start !important;
            padding: 12px 20px !important;
        }
        
        .sidebar.collapsed:hover .sidebar-brand {
            padding: 20px;
            justify-content: flex-start;
        }
        
        .sidebar.collapsed:hover .sidebar-brand-text {
            opacity: 1;
            width: auto;
        }
        
        /* Keep toggle button in same position when hover */
        .sidebar.collapsed:hover .sidebar-toggle-btn {
            right: 21px;  /* Stay in same position */
        }

        /* Sidebar Navigation */
        .sidebar .nav {
            padding: 20px 0;
        }

        .sidebar .nav-link {
            color: #475569 !important;
            padding: 12px 20px !important;
            margin: 5px 0 !important;
            border-left: 3px solid transparent !important;
            border-radius: 0 !important;
            transition: all 0.3s !important;
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            white-space: nowrap !important;
            cursor: pointer !important;
            user-select: none !important;
        }

        .sidebar .nav-link i {
            min-width: 20px !important;
            text-align: center !important;
            color: #64748b !important;
        }

        .sidebar .nav-text {
            transition: opacity 0.3s ease, width 0.3s ease !important;
        }

        .sidebar .nav-link:hover {
            background-color: #f1f5f9 !important;
            border-left-color: var(--primary) !important;
            cursor: pointer !important;
        }
        
        .sidebar .nav-link:hover i {
            color: var(--primary) !important;
        }

        .sidebar .nav-link.active {
            background: var(--primary) !important;
            border-left-color: var(--primary) !important;
            color: #ffffff !important;
            font-weight: 600 !important;
            box-shadow: 0 2px 8px rgba(var(--primary-rgb), 0.3) !important;
            cursor: pointer !important;
        }
        
        .sidebar .nav-link.active i {
            color: #ffffff !important;
        }

        /* Submenu Dropdown Styles */
        .sidebar .nav-item.has-submenu > .nav-link {
            position: relative;
        }

        .sidebar .submenu-arrow {
            position: absolute;
            right: 20px;
            font-size: 12px;
            transition: transform 0.3s ease;
        }

        .sidebar .nav-link[aria-expanded="true"] .submenu-arrow {
            transform: rotate(180deg);
        }

        .sidebar.collapsed .submenu-arrow {
            display: none;
        }

        .sidebar .submenu {
            list-style: none;
            padding: 0;
            margin: 0;
            background: #f8fafc;
        }

        .sidebar .submenu li {
            margin: 0;
        }

        .sidebar .submenu-link {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            padding: 10px 20px 10px 45px !important;
            color: #64748b !important;
            text-decoration: none !important;
            transition: all 0.3s !important;
            font-size: 14px !important;
            white-space: nowrap !important;
            cursor: pointer !important;
        }

        .sidebar .submenu-link i {
            min-width: 18px !important;
            text-align: center !important;
            font-size: 14px !important;
            color: #94a3b8 !important;
        }

        .sidebar .submenu-link:hover {
            background: #e2e8f0 !important;
            color: var(--primary) !important;
            padding-left: 50px !important;
        }
        
        .sidebar .submenu-link:hover i {
            color: var(--primary) !important;
        }

        .sidebar .submenu-link.active {
            background: #e0e7ff !important;
            color: var(--primary) !important;
            font-weight: 600 !important;
        }
        
        .sidebar .submenu-link.active i {
            color: var(--primary) !important;
        }

        /* Hide submenu when sidebar collapsed */
        .sidebar.collapsed .submenu {
            display: none !important;
        }

        /* Show submenu on hover when collapsed */
        .sidebar.collapsed:hover .submenu.show {
            display: block !important;
        }

        .sidebar.collapsed:hover .submenu-arrow {
            display: inline-block;
        }

        .sidebar.collapsed:hover .nav-link[aria-expanded="true"] .submenu-arrow {
            transform: rotate(180deg);
        }

        /* Main Content Area */
        .main-wrapper {
            flex: 1;
            margin-left: 250px;
            transition: none; /* Disable transition on page load */
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            padding-left: 12px; /* Add gap between sidebar and content */
        }
        
        /* Enable transitions after page load */
        .main-wrapper.transitions-enabled {
            transition: margin-left 0.3s ease;
        }

        .sidebar.collapsed + .main-wrapper {
            margin-left: 70px;
        }

        /* Navbar in Content Area */
        .navbar,
        .admin-navbar {
            min-height: 68px !important;
            background: var(--navbar-bg, #ffffff) !important;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05) !important;
            position: sticky;
            top: 0;
            z-index: 999;
            border-bottom: 1px solid var(--border-color, #e2e8f0) !important;
        }
        
        /* Dark mode navbar */
        [data-theme="dark"] .navbar,
        [data-theme="dark"] .admin-navbar {
            --navbar-bg: #1e293b;
            --border-color: #334155;
        }

        .navbar > .container-fluid {
            min-height: 68px !important;
            padding-left: 24px !important;
            padding-right: 24px !important;
        }

        .navbar-brand {
            display: inline-flex !important;
            align-items: center !important;
            gap: 12px !important;
            min-height: 40px !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
            font-weight: 700 !important;
            font-size: 18px !important;
            line-height: 1.2 !important;
            white-space: normal !important;
            max-width: 340px !important;
            text-decoration: none !important;
            color: #1e293b !important;
            transition: opacity 0.3s !important;
        }
        
        .navbar-brand:hover {
            opacity: 0.9;
        }

        .navbar-brand .brand-mark {
            width: 42px;
            height: 42px;
            min-width: 42px;
            border-radius: 14px;
            display: inline-grid;
            place-items: center;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
        }

        .navbar-brand .brand-mark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            border-radius: 12px;
            padding: 4px;
            background: rgba(255,255,255,0.95);
        }
        
        .navbar-brand .brand-mark i {
            font-size: 20px;
            color: #ffffff;
        }

        .navbar-brand .brand-text {
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 2px;
            color: #1e293b;
        }

        .navbar-brand .brand-subtitle {
            font-size: 0.65rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            opacity: 0.7;
            margin: 0;
            color: #64748b;
        }

        .navbar-brand strong {
            font-size: 1rem;
            font-weight: 800;
            line-height: 1.1;
            display: block;
            color: #1e293b;
        }

        .navbar-brand .brand-year {
            font-size: 0.75rem;
            opacity: 0.7;
            margin: 0;
            color: #64748b;
        }
        
        /* Navbar Title (Static + Dynamic) */
        .navbar-title {
            display: flex;
            flex-direction: column;
            gap: 4px;
            color: #1e293b !important;
        }
        
        .navbar-title-main {
            font-size: 16px;
            font-weight: 700;
            line-height: 1.2;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #1e293b !important;
        }
        
        .navbar-title-sub {
            font-size: 13px;
            font-weight: 500;
            opacity: 0.85;
            display: flex;
            align-items: center;
            gap: 8px;
            color: #64748b !important;
        }
        
        .school-name {
            font-weight: 600;
            color: #1e293b !important;
        }
        
        .separator {
            opacity: 0.6;
            color: #64748b !important;
        }
        
        .year-text {
            font-weight: 500;
            color: #64748b !important;
        }
        
        /* Hide navbar title on screens 960px and below */
        @media (max-width: 960px) {
            .navbar-title {
                display: none !important;
            }
        }

        /* Content Area */
        .main-content {
            width: 100%;
            max-width: 1280px;
            margin: 0 auto;
            padding: 32px 28px !important;
            min-width: 0;
            flex: 1;
        }

        .user-info {
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
            color: #1e293b !important;
            min-height: 40px !important;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif !important;
        }

        .user-info small,
        .user-info div {
            line-height: 1.2 !important;
        }

        .user-avatar {
            width: 40px !important;
            height: 40px !important;
            min-width: 40px !important;
            background: linear-gradient(135deg, var(--primary), var(--secondary)) !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #ffffff !important;
        }

        .admin-theme-toggle {
            min-height: 34px !important;
            line-height: 1 !important;
            white-space: nowrap !important;
        }

        .admin-page-title,
        .main-content h1,
        .main-content h2 {
            color: #2c3e50;
        }

        /* Mobile Responsive */
        @media (max-width: 991px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.mobile-show {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-left: 0 !important;
                padding-left: 0 !important; /* Remove gap on mobile */
            }
        }

        @media (max-width: 768px) {
            .navbar > .container-fluid {
                align-items: flex-start !important;
            }

            .navbar-brand {
                max-width: calc(100vw - 74px) !important;
                font-size: 15px !important;
                white-space: normal !important;
                line-height: 1.25 !important;
            }
            
            .navbar-title {
                max-width: calc(100vw - 200px);
            }
            
            .navbar-title-main {
                font-size: 13px;
            }
            
            .navbar-title-sub {
                font-size: 11px;
                flex-wrap: wrap;
            }

            .main-content {
                max-width: none;
                margin: 0;
                padding: 18px 12px !important;
            }
        }

        /* Mobile Overlay */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
        }

        .sidebar-overlay.show {
            display: block;
        }

        /* Mobile Menu Button - Only show on mobile (<992px) */
        .admin-mobile-menu-btn {
            display: none !important;
            padding: 10px 14px;
            border: 2px solid #cbd5e1 !important;
            border-radius: 8px;
            background: #f1f5f9 !important;
            color: #1e293b !important;
            font-size: 22px;
            font-weight: 700;
            transition: all 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08) !important;
            cursor: pointer;
        }
        
        /* Dark Theme - Tombol hamburger putih */
        [data-theme="dark"] .admin-mobile-menu-btn {
            border: 2px solid rgba(255, 255, 255, 0.3) !important;
            background: rgba(255, 255, 255, 0.15) !important;
            color: #ffffff !important;
        }
        
        .admin-mobile-menu-btn:hover {
            background: #e2e8f0 !important;
            border-color: #94a3b8 !important;
            transform: scale(1.05);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.12) !important;
        }
        
        [data-theme="dark"] .admin-mobile-menu-btn:hover {
            background: rgba(255, 255, 255, 0.25) !important;
            border-color: rgba(255, 255, 255, 0.5) !important;
        }
        
        .admin-mobile-menu-btn:active {
            transform: scale(0.98);
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15) !important;
        }

        @media (max-width: 991px) {
            .admin-mobile-menu-btn {
                display: inline-flex !important;
                align-items: center;
                justify-content: center;
            }
        }

        /* User Dropdown Positioning Fix */
        .user-dropdown-wrapper .dropdown-menu {
            margin-top: 16px !important;
        }

        /* Ensure no weird boxes appear */
        .navbar .container-fluid {
            align-items: center;
        }
        
        /* Fix Bootstrap table-light for light theme consistency */
        .table-light {
            --bs-table-bg: var(--bg-secondary) !important;
            --bs-table-color: var(--text-secondary) !important;
        }
        
        .table-light th {
            color: var(--text-secondary) !important;
            font-weight: 700 !important;
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    @include('partials.admin-sidebar')

    <!-- Sidebar Overlay for Mobile -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <!-- Navbar -->
        @include('partials.admin-navbar')

        <!-- Main Content -->
        <main class="main-content">
            @yield('content')
        </main>
    </div>

    <!-- Toast Container -->
    <x-toast-container position="top-right" />

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <script src="{{ asset('js/modal.js') }}?v={{ config('app.version', time()) }}"></script>
    
    <script>
        // Debug: Check if Modal.js loaded successfully
        if (typeof Modal === 'undefined') {
            console.error('❌ Modal.js failed to load! Check network tab.');
        } else {
            console.log('✅ Modal.js loaded successfully');
        }
    </script>
    
    <script>
    // Sidebar Toggle Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const sidebar = document.getElementById('adminSidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const mainWrapper = document.querySelector('.main-wrapper');
        const mobileMenuBtn = document.querySelector('.admin-mobile-menu-btn');
        const overlay = document.getElementById('sidebarOverlay');
        
        // Load saved state from localStorage
        const sidebarCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (sidebarCollapsed && window.innerWidth >= 992) {
            sidebar.classList.add('collapsed');
        }
        
        // Enable transitions after initial state is set
        setTimeout(function() {
            sidebar.classList.add('transitions-enabled');
            mainWrapper.classList.add('transitions-enabled');
        }, 50);
        
        // Initialize Bootstrap tooltips for collapsed state
        let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        let tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl, {
                trigger: sidebar.classList.contains('collapsed') ? 'hover' : 'manual'
            });
        });
        
        // Toggle sidebar on button click (Desktop)
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                const isCollapsed = sidebar.classList.contains('collapsed');
                
                // Save state to localStorage
                localStorage.setItem('sidebarCollapsed', isCollapsed);
                
                // Update HTML class for CSS
                if (isCollapsed) {
                    document.documentElement.classList.add('sidebar-collapsed');
                } else {
                    document.documentElement.classList.remove('sidebar-collapsed');
                }
                
                // Update tooltips
                tooltipList.forEach(function(tooltip) {
                    if (isCollapsed) {
                        tooltip.enable();
                    } else {
                        tooltip.disable();
                    }
                });
            });
        }

        // Mobile menu toggle
        if (mobileMenuBtn) {
            mobileMenuBtn.addEventListener('click', function() {
                sidebar.classList.add('mobile-show');
                overlay.classList.add('show');
            });
        }

        // Close sidebar on overlay click
        if (overlay) {
            overlay.addEventListener('click', function() {
                sidebar.classList.remove('mobile-show');
                overlay.classList.remove('show');
            });
        }
        
        // Disable tooltips initially if sidebar is expanded
        if (!sidebarCollapsed) {
            tooltipList.forEach(function(tooltip) {
                tooltip.disable();
            });
        }
    });
    </script>
    
    <!-- Form Validation -->
    <script src="{{ asset('js/form-validation.js?v=' . time()) }}"></script>
    
    @stack('scripts')
</body>
</html>
