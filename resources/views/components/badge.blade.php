@props(['tone' => 'gray'])

@php
    $tones = [
        'gray' => 'bg-gray-100 text-gray-700 ring-gray-500/10',
        'brand' => 'bg-brand-50 text-brand-700 ring-brand-600/20',
        'green' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/20',
        'amber' => 'bg-amber-50 text-amber-700 ring-amber-600/20',
        'red' => 'bg-red-50 text-red-700 ring-red-600/20',
        'sky' => 'bg-sky-50 text-sky-700 ring-sky-600/20',
    ];
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full px-2.5 py-0.5 text-xs font-medium ring-1 ring-inset ' . $tones[$tone]]) }}>
    {{ $slot }}
</span>
