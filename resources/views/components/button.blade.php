@props(['variant' => 'primary', 'size' => 'md', 'type' => 'submit', 'href' => null])

@php
    $variants = [
        'primary' => 'bg-brand-600 text-white shadow-sm hover:bg-brand-500 focus-visible:outline-brand-600',
        'secondary' => 'bg-white text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 focus-visible:outline-gray-600',
        'danger' => 'bg-red-600 text-white shadow-sm hover:bg-red-500 focus-visible:outline-red-600',
        'success' => 'bg-emerald-600 text-white shadow-sm hover:bg-emerald-500 focus-visible:outline-emerald-600',
        'ghost' => 'text-gray-700 hover:bg-gray-100 hover:text-gray-900 focus-visible:outline-gray-600',
        'link' => 'text-brand-600 hover:text-brand-500 focus-visible:outline-brand-600',
    ];

    $sizes = [
        'sm' => 'px-2.5 py-1.5 text-xs',
        'md' => 'px-3.5 py-2 text-sm',
        'lg' => 'px-4 py-2.5 text-sm',
    ];

    $classes = $variants[$variant];
    if ($variant !== 'link') {
        $classes .= ' ' . $sizes[$size];
    }

    $classes = 'inline-flex items-center justify-center gap-2 rounded-lg font-semibold transition focus-visible:outline-2 focus-visible:outline-offset-2 disabled:pointer-events-none disabled:opacity-50 ' . $classes;
@endphp

@if ($href)
    <a href="{{ $href }}" wire:navigate {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button
        type="{{ $type }}"
        wire:loading.attr="disabled"
        {{ $attributes->merge(['class' => $classes]) }}
    >
        {{ $slot }}
    </button>
@endif
