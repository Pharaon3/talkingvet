import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import path from 'path'

export default defineConfig({
    plugins: [

        laravel({

            input: [
                'resources/scss/libs-override/flatpicker-confirm-date.css',
                'resources/scss/app.scss',
                'resources/js/app.js',
                'resources/js/pages/dashboard.js',
                'resources/js/pages/review.js',
                'resources/js/encounters/mic-test.js'
                // 'resources/lib/ableplayer-4.5/build/ableplayer.min.css',
                // 'resources/lib/ableplayer-4.5/build/ableplayer.min.js',
                // 'resources/lib/ableplayer-4.5/build/translations/en.js',
            ],
            refresh: true,
            terserOptions: {
                // Exclude specific files from minification
                exclude: ['resources/js/app.js']
            }
        }),
    ],
    resolve: {
        alias: {
            '~bootstrap': path.resolve(__dirname, 'node_modules/bootstrap'),
            '~flatpickr': path.resolve(__dirname, 'node_modules/flatpickr'),
            '~moment': path.resolve(__dirname, 'node_modules/moment'),
        }
    }
});
