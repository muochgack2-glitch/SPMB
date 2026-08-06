/**
 * ============================================
 * DYNAMIC NAVBAR - SCROLL BEHAVIOR
 * ============================================
 */

document.addEventListener('DOMContentLoaded', function() {
    const navbar = document.getElementById('dynamicNavbar');
    if (!navbar) return;
    
    let lastScrollTop = 0;
    let scrollThreshold = 100; // Start hiding after 100px
    let isNavbarHidden = false;
    
    window.addEventListener('scroll', function() {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Scroll down & past threshold → hide navbar
        if (scrollTop > lastScrollTop && scrollTop > scrollThreshold) {
            if (!isNavbarHidden) {
                navbar.classList.add('navbar-hidden');
                isNavbarHidden = true;
            }
        }
        // Scroll up → show navbar
        else if (scrollTop < lastScrollTop) {
            if (isNavbarHidden) {
                navbar.classList.remove('navbar-hidden');
                isNavbarHidden = false;
            }
        }
        
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }, { passive: true });
    
    
    /**
     * Keyboard Shortcut: Ctrl+K for Search
     */
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const searchInput = document.querySelector('.search-input');
            if (searchInput) {
                searchInput.focus();
            }
        }
    });
    
    
    /**
     * Dark Mode Icon Sync (Navbar)
     */
    function updateNavbarDarkModeIcon() {
        const isDark = document.documentElement.classList.contains('dark');
        const moonIcon = document.getElementById('navbar-icon-moon');
        const sunIcon = document.getElementById('navbar-icon-sun');
        
        if (moonIcon && sunIcon) {
            if (isDark) {
                moonIcon.classList.add('hidden');
                sunIcon.classList.remove('hidden');
            } else {
                moonIcon.classList.remove('hidden');
                sunIcon.classList.add('hidden');
            }
        }
    }
    
    // Initialize icon on load
    updateNavbarDarkModeIcon();
    
    // Update icon on dark mode toggle
    const darkModeToggle = document.getElementById('navbarDarkModeToggle');
    if (darkModeToggle) {
        darkModeToggle.addEventListener('click', function() {
            setTimeout(updateNavbarDarkModeIcon, 50);
        });
    }
});
