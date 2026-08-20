<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Struct CRM') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-white text-gray-900">
        <div class="bg-gradient-to-b from-brand-50 via-white to-white">
            <header class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="flex h-16 items-center justify-between">
                    <a href="/" class="flex items-center gap-2.5">
                        <span class="flex size-9 items-center justify-center rounded-xl bg-brand-600 text-white">
                            <x-application-logo class="size-5" />
                        </span>
                        <span class="text-base font-bold tracking-tight text-gray-900">{{ config('app.name', 'Struct CRM') }}</span>
                    </a>

                    <nav class="flex items-center gap-2 sm:gap-3">
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                            >
                                Open dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="rounded-lg px-3 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-100 hover:text-gray-900">
                                Log in
                            </a>

                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="inline-flex items-center justify-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-brand-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600"
                                >
                                    Get started
                                </a>
                            @endif
                        @endauth
                    </nav>
                </div>
            </header>

            <main>
                <section class="mx-auto max-w-7xl px-4 pb-20 pt-20 sm:px-6 sm:pt-28 lg:px-8">
                    <div class="mx-auto max-w-3xl text-center">
                        <span class="inline-flex items-center gap-2 rounded-full bg-brand-100 px-3 py-1 text-xs font-semibold uppercase tracking-wide text-brand-700">
                            Construction project management & CRM
                        </span>

                        <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-gray-900 sm:text-5xl lg:text-6xl">
                            Run every project, bid, and bill from one place.
                        </h1>

                        <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-gray-600">
                            {{ config('app.name', 'Struct CRM') }} replaces spreadsheets and paper logs with a single cloud platform for general contractors, home builders, and specialty subcontractors.
                        </p>

                        <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="inline-flex w-full items-center justify-center rounded-xl bg-brand-600 px-6 py-3 text-base font-semibold text-white shadow-sm transition hover:bg-brand-500 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-brand-600 sm:w-auto"
                                >
                                    Start your free trial
                                </a>
                            @endif

                            <a
                                href="{{ route('login') }}"
                                class="inline-flex w-full items-center justify-center rounded-xl bg-white px-6 py-3 text-base font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 transition hover:bg-gray-50 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-gray-600 sm:w-auto"
                            >
                                Log in
                            </a>
                        </div>
                    </div>
                </section>

                <section class="mx-auto max-w-7xl px-4 pb-24 sm:px-6 lg:px-8">
                    <div class="mx-auto max-w-2xl text-center">
                        <h2 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Everything your crew and office need</h2>
                        <p class="mt-4 text-lg leading-8 text-gray-600">From the first lead to the final invoice, keep every detail in sync across the field and back office.</p>
                    </div>

                    <div class="mx-auto mt-16 grid max-w-6xl grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @php
                            $features = [
                                ['title' => 'Projects & contracts', 'description' => 'Track project numbers, clients, contract values, and change orders in one view.', 'icon' => 'M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21'],
                                ['title' => 'Estimates & invoices', 'description' => 'Send branded quotations, convert them to contracts, and bill progress with retention.', 'icon' => 'M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.65 4.5 4.757V19.5a2.25 2.25 0 0 0 2.25 2.25h10.5a2.25 2.25 0 0 0 2.25-2.25V4.757c0-1.108-.806-2.057-1.907-2.185A48.507 48.507 0 0 0 12 2.25Z'],
                                ['title' => 'Daily logs & site photos', 'description' => 'Capture weather, crew counts, work performed, and progress photos from the field.', 'icon' => 'M9 12h3.75M9 15h3.75M9 18h3.75M15.75 18H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z'],
                                ['title' => 'Budgets & cost codes', 'description' => 'Keep budgets, BOQ line items, and cost codes aligned so margins stay on target.', 'icon' => 'M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z'],
                                ['title' => 'Time tracking', 'description' => 'Clock in from the job site and roll hours straight into payroll and project costs.', 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z'],
                                ['title' => 'Fleet & equipment', 'description' => 'Schedule maintenance, log service history, and know when equipment is due.', 'icon' => 'M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.902 17.902 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-11.177v-.958c0-.568-.422-1.048-.987-1.106a48.554 48.554 0 0 0-10.026 0 1.106 1.106 0 0 0-.987 1.106v7.635m12-6.677v6.677m0 4.5v-4.5m0 0h-12'],
                            ];
                        @endphp

                        @foreach ($features as $feature)
                            <div class="rounded-2xl bg-white p-6 shadow-sm ring-1 ring-gray-900/5 transition hover:shadow-md">
                                <div class="flex size-11 items-center justify-center rounded-lg bg-brand-50 text-brand-600">
                                    <svg class="size-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $feature['icon'] }}" />
                                    </svg>
                                </div>
                                <h3 class="mt-4 text-base font-semibold text-gray-900">{{ $feature['title'] }}</h3>
                                <p class="mt-2 text-sm leading-6 text-gray-600">{{ $feature['description'] }}</p>
                            </div>
                        @endforeach
                    </div>
                </section>

                <section class="mx-auto max-w-7xl px-4 pb-24 sm:px-6 lg:px-8">
                    <div class="overflow-hidden rounded-3xl bg-brand-600 px-6 py-16 text-center sm:px-16">
                        <h2 class="text-3xl font-bold tracking-tight text-white">Ready to get off the spreadsheets?</h2>
                        <p class="mx-auto mt-4 max-w-xl text-lg leading-8 text-brand-100">
                            Start managing your projects, budgets, and billing in one place. Free to try, no credit card required.
                        </p>
                        <div class="mt-8">
                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="inline-flex items-center justify-center rounded-xl bg-white px-6 py-3 text-base font-semibold text-brand-700 shadow-sm transition hover:bg-brand-50"
                                >
                                    Get started
                                </a>
                            @endif
                        </div>
                    </div>
                </section>
            </main>

            <footer class="border-t border-gray-200 bg-white">
                <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-4 py-10 sm:flex-row sm:px-6 lg:px-8">
                    <div class="flex items-center gap-2.5">
                        <span class="flex size-7 items-center justify-center rounded-lg bg-brand-600 text-white">
                            <x-application-logo class="size-4" />
                        </span>
                        <span class="text-sm font-semibold text-gray-900">{{ config('app.name', 'Struct CRM') }}</span>
                    </div>
                    <p class="text-sm text-gray-500">&copy; {{ date('Y') }} {{ config('app.name', 'Struct CRM') }}. All rights reserved.</p>
                </div>
            </footer>
        </div>
    </body>
</html>
