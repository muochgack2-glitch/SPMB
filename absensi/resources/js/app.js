// Import Alpine.js (standalone, not via Livewire)
import Alpine from 'alpinejs';

// Initialize Dark Mode immediately (before Alpine loads)
(function() {
    // Apply dark mode from localStorage immediately to prevent flash
    const isDark = localStorage.getItem('darkMode') === 'true';
    if (isDark) {
        document.documentElement.classList.add('dark');
    }
    console.log('Early dark mode applied:', isDark ? 'Dark' : 'Light');
})();

// Register Dark Mode Store before Alpine starts
Alpine.store('darkMode', {
    // Initialize from localStorage
    isDark: localStorage.getItem('darkMode') === 'true',
    
    // Toggle dark mode
    toggle() {
        this.isDark = !this.isDark;
        this.apply();
        console.log('Dark mode toggled to:', this.isDark ? 'Dark' : 'Light');
    },
    
    // Enable dark mode
    enable() {
        this.isDark = true;
        this.apply();
    },
    
    // Disable dark mode (light mode)
    disable() {
        this.isDark = false;
        this.apply();
    },
    
    // Apply current theme
    apply() {
        // Update localStorage
        localStorage.setItem('darkMode', this.isDark);
        
        // Update DOM class
        if (this.isDark) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        
        // Dispatch event for other components
        window.dispatchEvent(new CustomEvent('darkmode-changed', { 
            detail: { isDark: this.isDark } 
        }));
        
        console.log('Dark mode applied:', this.isDark ? 'Dark' : 'Light', 'HTML has dark class:', document.documentElement.classList.contains('dark'));
    }
});

console.log('Dark Mode Store registered');

// Make Alpine available globally
window.Alpine = Alpine;

// Start Alpine
Alpine.start();

console.log('Alpine started');

// Import and export html5-qrcode for scanner page
import { Html5Qrcode } from 'html5-qrcode';
window.Html5Qrcode = Html5Qrcode;
console.log('Html5Qrcode loaded:', typeof Html5Qrcode);

// Import Toast notification system
import './toast.js';
