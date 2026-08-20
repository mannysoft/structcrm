@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full border-l-4 border-brand-600 bg-brand-50 ps-3 pe-4 py-2 text-start text-sm font-medium text-brand-700 transition duration-150 ease-in-out focus:border-brand-700 focus:bg-brand-100 focus:text-brand-800 focus:outline-none'
            : 'block w-full border-l-4 border-transparent ps-3 pe-4 py-2 text-start text-sm font-medium text-gray-600 transition duration-150 ease-in-out hover:border-gray-300 hover:bg-gray-50 hover:text-gray-900 focus:border-gray-300 focus:bg-gray-50 focus:text-gray-900 focus:outline-none';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
