import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/sidebar.js',
                'resources/js/navbar.js',
            ],
            refresh: [
                'resources/views/**/*.blade.php',
                'routes/**',
                'app/Http/Controllers/**',
            ],
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (id.includes('alpinejs')) {
                        return 'vendor';
                    }
                    if (id.includes('html5-qrcode')) {
                        return 'qr';
                    }
                }
            }
        }
    },
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
