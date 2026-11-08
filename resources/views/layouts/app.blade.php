<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ isset($title) ? $title . ' · ' . config('app.name') : config('app.name') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body class="font-sans antialiased bg-gray-50 text-gray-900">
        <livewire:layout.navigation />

        <div class="pt-16 lg:pl-64 lg:pt-0">
            <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8 lg:py-10">
                @if (isset($header))
                    <div class="mb-6">{{ $header }}</div>
                @endif

                {{ $slot }}
            </main>
        </div>

        <!-- Toasts -->
        @if (session('status') || $errors->any())
            <div class="pointer-events-none fixed inset-x-0 top-0 z-[60] flex flex-col items-center gap-3 px-4 pt-4 sm:items-end sm:pr-6">
                @if (session('status'))
                    <x-toast type="success" :message="session('status')" />
                @endif

                @if ($errors->any())
                    <x-toast type="error" message="{{ __('Please review the form and fix the highlighted errors.') }}" />
                @endif
            </div>
        @endif

        @if (auth()->check() && ! auth()->user()->isClient())
            @persist('assistant-drawer')
                <livewire:assistant.drawer />
            @endpersist
        @endif

        @livewireScripts
    </body>
</html>
