// Livewire 3 already includes Alpine.js
// Alpine.js is available via window.Alpine from Livewire

// Initialize Dark Mode immediately (before Livewire loads)
(function() {
    // Apply dark mode from localStorage immediately to prevent flash
    const isDark = localStorage.getItem('darkMode') === 'true';
    if (isDark) {
        document.documentElement.classList.add('dark');
    }
    console.log('Early dark mode applied:', isDark ? 'Dark' : 'Light');
})();

// Wait for Alpine to be available
document.addEventListener('alpine:init', () => {
    console.log('Alpine:init event fired - Setting up dark mode store');
    
    // Register Dark Mode Store
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
});

// Fallback: Also listen for livewire:init
document.addEventListener('livewire:init', () => {
    console.log('Livewire:init event fired');
});

console.log('App JS loaded - Waiting for Alpine');

// Import Toast notification system
import './toast.js';
