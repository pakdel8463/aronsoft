import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                // Global
                'resources/css/fonts.css',
                'resources/css/app.css',
                'resources/js/app.js',

                // Admin (/panel) — Tailwind v3, tokens live in tailwind.config.js
                'resources/css/admin.css',
                'resources/js/admin.js',

                // Dashboard
                'resources/css/dashboard.css',
                'resources/js/dashboard.js',

                // Front (اضافه شده)
                'resources/css/front.css',
                'resources/js/front.js',
            ],
            refresh: true,
        }),
    ],
});
