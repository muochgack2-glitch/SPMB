/**
 * Toast Notification System
 * Global API for showing success, error, info, and warning notifications
 */

window.Toast = {
    /**
     * Show success notification
     * @param {string} message - The message to display
     * @param {string} title - The title (default: 'Berhasil')
     */
    success(message, title = 'Berhasil') {
        this.show(message, title, 'success');
    },

    /**
     * Show error notification
     * @param {string} message - The message to display
     * @param {string} title - The title (default: 'Error')
     */
    error(message, title = 'Error') {
        this.show(message, title, 'error');
    },

    /**
     * Show info notification
     * @param {string} message - The message to display
     * @param {string} title - The title (default: 'Info')
     */
    info(message, title = 'Info') {
        this.show(message, title, 'info');
    },

    /**
     * Show warning notification
     * @param {string} message - The message to display
     * @param {string} title - The title (default: 'Peringatan')
     */
    warning(message, title = 'Peringatan') {
        this.show(message, title, 'warning');
    },

    /**
     * Show notification with custom type
     * @param {string} message - The message to display
     * @param {string} title - The title
     * @param {string} type - The type (success, error, info, warning)
     */
    show(message, title, type) {
        const toast = {
            id: Date.now(),
            title,
            message,
            type,
            show: true,
            progress: 100
        };

        // Dispatch custom event to add toast to container
        window.dispatchEvent(new CustomEvent('toast-show', { detail: toast }));

        // Auto-dismiss after 5 seconds with progress animation
        const duration = 5000;
        const interval = 50;
        const decrement = (interval / duration) * 100;

        const progressTimer = setInterval(() => {
            toast.progress -= decrement;
            if (toast.progress <= 0) {
                clearInterval(progressTimer);
            }
        }, interval);

        setTimeout(() => {
            clearInterval(progressTimer);
            window.dispatchEvent(new CustomEvent('toast-hide', { detail: toast.id }));
        }, duration);
    }
};

/**
 * Alpine.js Toast Container Component
 * Manages the toast stack and animations
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('toastContainer', () => ({
        toasts: [],

        init() {
            // Listen for show event
            window.addEventListener('toast-show', (e) => {
                this.toasts.push(e.detail);
            });

            // Listen for hide event
            window.addEventListener('toast-hide', (e) => {
                const index = this.toasts.findIndex(t => t.id === e.detail);
                if (index > -1) {
                    this.toasts[index].show = false;
                    // Remove from array after animation completes
                    setTimeout(() => {
                        this.toasts.splice(index, 1);
                    }, 300);
                }
            });
        },

        remove(id) {
            window.dispatchEvent(new CustomEvent('toast-hide', { detail: id }));
        },

        getIcon(type) {
            const icons = {
                success: 'fa-check-circle',
                error: 'fa-times-circle',
                info: 'fa-info-circle',
                warning: 'fa-exclamation-triangle'
            };
            return icons[type] || 'fa-info-circle';
        },

        getColorClasses(type) {
            const colors = {
                success: 'bg-green-500 dark:bg-green-600',
                error: 'bg-red-500 dark:bg-red-600',
                info: 'bg-blue-500 dark:bg-blue-600',
                warning: 'bg-yellow-500 dark:bg-yellow-600'
            };
            return colors[type] || 'bg-gray-500';
        }
    }));
});
