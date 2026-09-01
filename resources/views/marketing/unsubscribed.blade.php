@component('layouts.guest')
    <h1 class="text-xl font-semibold text-gray-900">You've been unsubscribed</h1>

    <p class="mt-3 text-sm text-gray-600">
        You will no longer receive marketing emails from {{ config('app.name') }}.
    </p>

    <a href="/" class="mt-6 inline-flex items-center rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-500">
        Back to home
    </a>
@endcomponent
