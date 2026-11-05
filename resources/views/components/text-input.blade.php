@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 dark:border-line focus:border-lime-500 focus:ring-lime-500 rounded-md shadow-sm']) !!}>