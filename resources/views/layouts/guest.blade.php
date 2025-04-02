<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <link rel="stylesheet" href="{{ asset('assets/css/toastr.min.css') }}">
        <script src="{{ asset('assets/js/toastr.min.js') }}"></script>
        <!-- Scripts -->
        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 dark:text-white antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-gray-900">

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-600 shadow-md overflow-hidden sm:rounded-lg">
                <div class="flex justify-center py-2">
                    <a href="/">
                        <x-application-logo class="w-40 fill-current" />
                    </a>
                </div>
                <x-global-status-or-error />

                {{ $slot }}
            </div>
        </div>

        @livewireScripts

    </body>
</html>
