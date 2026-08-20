@props(['type' => 'success', 'message'])

@php
    $styles = [
        'success' => 'bg-emerald-100 text-emerald-700',
        'error' => 'bg-red-100 text-red-700',
        'warning' => 'bg-amber-100 text-amber-700',
    ];

    $icons = [
        'success' => 'M5 13l4 4L19 7',
        'error' => 'M6 18L18 6M6 6l12 12',
        'warning' => 'M12 9v4m0 4h.01',
    ];
@endphp

<div
    x-data="{ show: true }"
    x-show="show"
    x-init="setTimeout(() => show = false, 4500)"
    x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="opacity-0 translate-y-2"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave="transition ease-in duration-200"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-xl bg-white shadow-lg ring-1 ring-black/5"
>
    <div class="p-4">
        <div class="flex items-start gap-3">
            <div class="flex size-7 shrink-0 items-center justify-center rounded-full {{ $styles[$type] }}">
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="{{ $icons[$type] }}" />
                </svg>
            </div>
            <div class="flex-1 pt-0.5 text-sm font-medium text-gray-900">{{ $message }}</div>
            <button type="button" @click="show = false" class="shrink-0 rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-500">
                <span class="sr-only">{{ __('Dismiss') }}</span>
                <svg class="size-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </div>
</div>
