@props(['placeholder' => 'Search…', 'model' => 'search'])

<div class="relative">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
        <svg class="size-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
        </svg>
    </div>
    <input
        type="search"
        placeholder="{{ $placeholder }}"
        wire:model.live.debounce.300ms="{{ $model }}"
        {{ $attributes->merge(['class' => 'block w-full rounded-lg border-0 py-2 pl-10 pr-3 text-sm text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:leading-6']) }}
    >
</div>
