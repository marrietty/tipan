{{--
    TIPAN logo mark (heart + medical cross).
    Drop the asset at: public/images/logo.png  (square icon/mark).
    Callers pass sizing utilities, e.g. <x-application-logo class="h-8 w-auto" />.
    Legacy color utilities like `fill-current text-gray-800` are harmless on an <img>.
--}}
<img
    src="{{ asset('images/logo.png') }}"
    alt="{{ config('app.name', 'TIPAN') }} logo"
    {{ $attributes->merge(['class' => 'h-8 w-auto']) }}
/>
