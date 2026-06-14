<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-gray-500">Accounts</p>
        <h1 class="mt-1 text-3xl font-semibold text-gray-900">Add a doctor</h1>
        <p class="mt-2 text-gray-600 max-w-xl">
            Create the account and profile. The doctor can then log in and open their availability.
        </p>
    </x-slot>

    <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('admin.doctors.index') }}"
               class="text-sm font-medium text-gray-500 transition hover:text-gray-900">
                &larr; All doctors
            </a>
        </div>

        <div class="rounded-2xl bg-white border border-gray-200/80 p-8">
            <form method="POST" action="{{ route('admin.doctors.store') }}" class="space-y-5">
                @csrf

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="first_name" :value="__('First name')" />
                        <x-text-input id="first_name" name="first_name" type="text" class="block mt-1 w-full" :value="old('first_name')" required autofocus autocomplete="given-name" />
                        <x-input-error :messages="$errors->get('first_name')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="last_name" :value="__('Last name')" />
                        <x-text-input id="last_name" name="last_name" type="text" class="block mt-1 w-full" :value="old('last_name')" required autocomplete="family-name" />
                        <x-input-error :messages="$errors->get('last_name')" class="mt-2" />
                    </div>
                </div>

                <div>
                    <x-input-label for="specialization_id" :value="__('Specialization')" />
                    <select id="specialization_id" name="specialization_id" required
                            class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500">
                        <option value="" disabled {{ old('specialization_id') ? '' : 'selected' }}>Choose a specialization</option>
                        @foreach ($specializations as $specialization)
                            <option value="{{ $specialization->id }}" @selected(old('specialization_id') == $specialization->id)>
                                {{ $specialization->display_name }}
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('specialization_id')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="license_number" :value="__('License number')" />
                    <x-text-input id="license_number" name="license_number" type="text" class="block mt-1 w-full" :value="old('license_number')" required />
                    <x-input-error :messages="$errors->get('license_number')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="phone" :value="__('Phone (optional)')" />
                    <x-text-input id="phone" name="phone" type="text" class="block mt-1 w-full" :value="old('phone')" autocomplete="tel" />
                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                </div>

                <hr class="border-gray-100">

                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" name="email" type="email" class="block mt-1 w-full" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="password" :value="__('Password')" />
                        <x-text-input id="password" name="password" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="password_confirmation" :value="__('Confirm password')" />
                        <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="block mt-1 w-full" required autocomplete="new-password" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('admin.doctors.index') }}" class="text-sm font-medium text-gray-500 transition hover:text-gray-900">
                        Cancel
                    </a>
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                        Create doctor
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
