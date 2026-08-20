@props(['disabled' => false])

<select
    @disabled($disabled)
    {{ $attributes->merge(['class' => 'block w-full rounded-lg border-0 py-2 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-brand-600 sm:text-sm sm:leading-6']) }}
>
    {{ $slot }}
</select>
