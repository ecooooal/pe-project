import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    server: {
        host: '0.0.0.0',
        port: 5173,
        hmr: {
            host: 'localhost',
            port: 5173,
        },
        watch: {
            ignored: [
                '**/storage/logs/**', // ignore Laravel logs
                '**/vendor/**',       // ignore Composer stuff too (optional)
            ],
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css', 
                'resources/js/app.js',
                'resources/js/text-editor.js',
                'resources/js/edit-coding-text-editor.js', 
                'resources/js/answering-text-editor.js'
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
