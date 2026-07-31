import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/charts.js',
                'resources/js/qr-scanner.js',
            ],
            refresh: [
                'resources/views/**/*.blade.php',
                'routes/**',
                'app/Http/Controllers/**',
                'app/Livewire/**',
            ],
            fonts: [
                bunny('Instrument Sans', {
                    weights: [400, 500, 600],
                }),
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
                    if (id.includes('apexcharts')) {
                        return 'charts';
                    }
                    if (id.includes('html5-qrcode')) {
                        return 'qr';
                    }
                    if (id.includes('flatpickr')) {
                        return 'date';
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
