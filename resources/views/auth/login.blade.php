<x-guest-layout>
    <x-slot name="heading">Let's get you signed in</x-slot>
    <x-slot name="subheading">Please sign in to your account</x-slot>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-danger/10 border border-danger/20 text-danger text-sm flex items-start gap-3">
            <svg class="h-5 w-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <div>
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full {{ $errors->has('email') || $errors->has('password') ? 'border-danger focus:border-danger focus:ring-danger' : '' }}" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
        </div>

        <!-- Password -->
        <div class="mt-4" x-data="{ show: false }">
            <x-input-label for="password" :value="__('Password')" />
            
            <div class="relative mt-1">
                <x-text-input id="password" class="block w-full pr-10 {{ $errors->has('email') || $errors->has('password') ? 'border-danger focus:border-danger focus:ring-danger' : '' }}"
                                x-bind:type="show ? 'text' : 'password'"
                                name="password"
                                required autocomplete="current-password" />
                <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 pr-3 flex items-center text-muted hover:text-body focus:outline-none">
                    <!-- Eye icon (hide) -->
                    <svg x-show="show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                    <!-- Eye icon (show) -->
                    <svg x-show="!show" x-cloak class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                </button>
            </div>
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-line text-primary shadow-sm focus:ring-primary" name="remember">
                <span class="ms-2 text-sm text-body">{{ __('Keep me signed in on this device') }}</span>
            </label>
        </div>

        @if (Route::has('password.request'))
            <div class="flex items-center justify-end mt-4">
                <a class="text-base font-medium text-primary hover:text-primary-dark rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary p-2 -mr-2" href="{{ route('password.request') }}">
                    {{ __('Forgot Password?') }}
                </a>
            </div>
        @endif

        <x-primary-button class="w-full mt-6 py-3 text-base">
            {{ __('Sign In') }}
        </x-primary-button>

        @if (Route::has('register'))
            <p class="mt-6 text-center text-sm text-body">
                {{ __("Don't have an account?") }}
                <a class="font-semibold text-primary hover:text-primary-dark" href="{{ route('register') }}">
                    {{ __('Sign Up') }}
                </a>
            </p>
        @endif
    </form>
</x-guest-layout>
