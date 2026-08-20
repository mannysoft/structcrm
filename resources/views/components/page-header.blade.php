@props(['title', 'description' => null])

<div class="sm:flex sm:items-center sm:justify-between">
    <div>
        <h1 class="text-2xl font-semibold tracking-tight text-gray-900">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-gray-500">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="mt-4 flex flex-col gap-3 sm:mt-0 sm:flex-row sm:items-center">
            {{ $actions }}
        </div>
    @endisset
</div>
