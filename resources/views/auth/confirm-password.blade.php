<x-guest-layout>
    <x-slot name="heading">Confirm Password</x-slot>

    <div class="mb-4 text-sm text-body">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <x-primary-button class="w-full mt-6 py-3 text-base">
            {{ __('Confirm') }}
        </x-primary-button>
    </form>
</x-guest-layout>
