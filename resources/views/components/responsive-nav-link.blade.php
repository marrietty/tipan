@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full ps-3 pe-4 py-2 border-l-4 border-primary text-start text-base font-medium text-primary bg-primary-indigo/40 focus:outline-none focus:text-primary-dark focus:bg-primary-indigo/60 focus:border-primary-dark transition duration-150 ease-in-out'
            : 'block w-full ps-3 pe-4 py-2 border-l-4 border-transparent text-start text-base font-medium text-body hover:text-heading hover:bg-canvas hover:border-line focus:outline-none focus:text-heading focus:bg-canvas focus:border-line transition duration-150 ease-in-out';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
