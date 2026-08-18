<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-gray-50">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'ConstructFlow') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Tailwind CDN (Since NPM is unavailable) -->
        <script src="https://cdn.tailwindcss.com"></script>
        
        <style>
            body { font-family: 'Inter', sans-serif; }
        </style>

        @livewireStyles
    </head>
    <body class="h-full font-sans antialiased text-gray-900">
        <div class="min-h-full">
            <nav class="bg-white border-b border-gray-200">
                <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex">
                            <div class="flex items-center flex-shrink-0">
                                <span class="text-xl font-bold text-indigo-600">ConstructFlow</span>
                            </div>
                            <div class="hidden sm:-my-px sm:ml-6 sm:flex sm:space-x-8">
                                <a href="/projects" class="inline-flex items-center px-1 pt-1 text-sm font-medium text-gray-900 border-b-2 border-indigo-500">Projects</a>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>

            <main>
                <div class="py-6 mx-auto max-w-7xl sm:px-6 lg:px-8">
                    {{ $slot }}
                </div>
            </main>
        </div>

        @livewireScripts
    </body>
</html>
