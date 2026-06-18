@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-heading']) }}>
    {{ $value ?? $slot }}
</label>
