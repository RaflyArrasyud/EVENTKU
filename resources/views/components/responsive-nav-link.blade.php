@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-lime-400 text-start text-base font-medium text-lime-600 bg-lime-50 focus:outline-none focus:text-lime-700 focus:bg-lime-100 focus:border-neutral-800 transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-gray-600 dark:text-neutral-400 hover:text-gray-800 dark:text-neutral-200 hover:bg-gray-50 dark:hover:bg-white/10 hover:border-gray-300 dark:border-line focus:outline-none focus:text-gray-800 dark:text-neutral-200 focus:bg-gray-50 dark:bg-white/5 focus:border-gray-300 dark:border-line transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>