<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <div class="flex min-h-screen flex-col items-center justify-center px-4 py-12 sm:px-6">
            <div class="flex flex-col items-center">
                <a href="/" wire:navigate class="flex items-center gap-2.5">
                    <span class="flex size-10 items-center justify-center rounded-xl bg-brand-600 text-white">
                        <x-application-logo class="size-6" />
                    </span>
                    <span class="text-lg font-semibold text-gray-900">{{ config('app.name') }}</span>
                </a>
            </div>

            <div class="mt-8 w-full max-w-md">
                <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 sm:p-8">
                    {{ $slot }}
                </div>
            </div>
        </div>

        @livewireScripts
    </body>
</html>
