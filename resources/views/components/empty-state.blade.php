@props(['title', 'description' => null])

<div class="flex flex-col items-center justify-center px-6 py-12 text-center">
    @isset($icon)
        <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
            {{ $icon }}
        </div>
    @endisset

    <h3 class="mt-4 text-sm font-semibold text-gray-900">{{ $title }}</h3>

    @if ($description)
        <p class="mt-1 max-w-sm text-sm text-gray-500">{{ $description }}</p>
    @endif

    @isset($action)
        <div class="mt-4">{{ $action }}</div>
    @endisset
</div>
