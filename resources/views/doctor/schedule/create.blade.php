<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-gray-500">Availability</p>
        <h1 class="mt-1 text-3xl font-semibold text-gray-900">Add a slot</h1>
        <p class="mt-2 text-gray-600 max-w-xl">
            Pick a date and a window of time. Patients will see it as an open time to book.
        </p>
    </x-slot>

    <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="rounded-2xl bg-white border border-gray-200/80 p-8">
            <form method="POST" action="{{ route('doctor.schedule.store') }}" class="space-y-6">
                @csrf

                <div>
                    <x-input-label for="available_date" :value="__('Date')" />
                    <x-text-input id="available_date" name="available_date" type="date"
                                  class="block mt-1 w-full"
                                  :value="old('available_date')"
                                  min="{{ now()->toDateString() }}"
                                  required autofocus />
                    <x-input-error :messages="$errors->get('available_date')" class="mt-2" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="slot_start" :value="__('Start time')" />
                        <x-text-input id="slot_start" name="slot_start" type="time"
                                      class="block mt-1 w-full"
                                      :value="old('slot_start')"
                                      required />
                        <x-input-error :messages="$errors->get('slot_start')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="slot_end" :value="__('End time')" />
                        <x-text-input id="slot_end" name="slot_end" type="time"
                                      class="block mt-1 w-full"
                                      :value="old('slot_end')"
                                      required />
                        <x-input-error :messages="$errors->get('slot_end')" class="mt-2" />
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('doctor.schedule.index') }}"
                       class="text-sm font-medium text-gray-500 transition hover:text-gray-900">
                        Cancel
                    </a>

                    {{-- Primary action. --}}
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                        Add slot
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
