const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        "./resources/**/*.js",
        './node_modules/tw-elements/dist/js/**/*.js',
        './node_modules/flatpickr/dist/js/**/*.js',

        // flowbite UI
        "./node_modules/flowbite/**/*.js",

        // livewire tables
        './vendor/rappasoft/laravel-livewire-tables/resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [
        require('@tailwindcss/forms'),
        require("tw-elements/dist/plugin.cjs"),
        require('flowbite/plugin')
    ],
    darkMode: "class",
};
