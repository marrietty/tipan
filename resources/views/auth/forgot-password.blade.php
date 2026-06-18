<x-guest-layout>
    <x-slot name="heading">Reset Password</x-slot>
    <x-slot name="subheading">We'll email you a reset link</x-slot>

    <div class="mb-4 text-sm text-body">
        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <x-primary-button class="w-full mt-6 py-3 text-base">
            {{ __('Email Password Reset Link') }}
        </x-primary-button>
    </form>
</x-guest-layout>
