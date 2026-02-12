import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/pages/operator.js',
                'resources/ts/app.tsx',
                'resources/ts/counter.tsx',
            ],
            refresh: true,
        }),
        react(),
        tailwindcss(),
    ],
    server: {
        host: '127.0.0.1',
        proxy: {
            '/api': {
                target: 'http://127.0.0.1:8000',
                changeOrigin: true,
                secure: false,
            },
            '/sanctum': {
                target: 'http://127.0.0.1:8000',
                changeOrigin: true,
                secure: false,
            }
        },
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
