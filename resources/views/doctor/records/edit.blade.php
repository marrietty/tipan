<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-gray-500">Visit record</p>
        <h1 class="mt-1 text-3xl font-semibold text-gray-900">
            {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}
        </h1>
        <p class="mt-2 text-gray-600 tabular-nums">
            {{ $appointment->schedule->available_date->format('l, F j, Y') }}
            &middot; {{ \Illuminate\Support\Carbon::parse($appointment->schedule->slot_start)->format('g:i A') }}
        </p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('doctor.appointments.index') }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-body transition hover:text-heading">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                All appointments
            </a>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-xl bg-primary-indigo/30 border border-primary-indigo px-4 py-3 text-sm text-primary-dark">
                {{ session('status') }}
            </div>
        @endif

        @if ($appointment->reason)
            <div class="mb-6 rounded-xl bg-gray-50 border border-gray-200/80 px-5 py-4">
                <div class="text-xs font-medium uppercase tracking-wide text-gray-500">Reason for visit</div>
                <p class="mt-1 text-sm text-gray-900">{{ $appointment->reason }}</p>
            </div>
        @endif

        {{-- Diagnosis & notes --}}
        <section class="rounded-2xl bg-white border border-gray-200/80 p-8">
            <h2 class="text-xl font-semibold text-gray-900">Diagnosis &amp; notes</h2>

            <form method="POST" action="{{ route('doctor.appointments.record.store', $appointment) }}" class="mt-5 space-y-5">
                @csrf

                <div>
                    <x-input-label for="diagnosis" :value="__('Diagnosis')" />
                    <textarea id="diagnosis" name="diagnosis" rows="2"
                              class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                              placeholder="Primary finding">{{ old('diagnosis', $record->diagnosis ?? '') }}</textarea>
                    <x-input-error :messages="$errors->get('diagnosis')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="notes" :value="__('Notes')" />
                    <textarea id="notes" name="notes" rows="5"
                              class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-primary focus:ring-primary"
                              placeholder="Observations, advice, follow-up">{{ old('notes', $record->notes ?? '') }}</textarea>
                    <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                </div>

                <div class="flex justify-end">
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3 text-base font-medium text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                        {{ $record ? 'Save changes' : 'Save record' }}
                    </button>
                </div>
            </form>
        </section>

        {{-- Prescriptions: only once a record exists to attach them to. --}}
        <section class="mt-8">
            <h2 class="text-xl font-semibold text-gray-900">Prescriptions</h2>

            @if (! $record)
                <p class="mt-2 text-gray-500">
                    Save the record first, then add any medications.
                </p>
            @else
                @if ($record->prescriptions->isEmpty())
                    <p class="mt-2 text-gray-500">No medications added yet.</p>
                @else
                    <ul class="mt-3 divide-y divide-gray-100 rounded-2xl bg-white border border-gray-200/80">
                        @foreach ($record->prescriptions as $rx)
                            <li class="flex items-start justify-between gap-4 px-5 py-4">
                                <div>
                                    <div class="font-medium text-gray-900">{{ $rx->medication_name }}</div>
                                    <div class="mt-0.5 text-sm text-gray-600">
                                        {{ $rx->dosage }} &middot; {{ $rx->frequency }}@if ($rx->duration_days) &middot; {{ $rx->duration_days }} {{ Str::plural('day', $rx->duration_days) }}@endif
                                    </div>
                                    @if ($rx->instructions)
                                        <div class="mt-1 text-sm text-gray-500">{{ $rx->instructions }}</div>
                                    @endif
                                </div>
                                <form method="POST" action="{{ route('doctor.prescriptions.destroy', $rx) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="text-sm font-medium text-gray-500 transition hover:text-red-700 focus:outline-none focus-visible:underline">
                                        Remove
                                    </button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif

                {{-- Add a medication --}}
                <div class="mt-4 rounded-2xl bg-white border border-gray-200/80 p-6">
                    <h3 class="font-medium text-gray-900">Add a medication</h3>
                    <form method="POST" action="{{ route('doctor.records.prescriptions.store', $record) }}" class="mt-4 space-y-4">
                        @csrf
                        <div>
                            <x-input-label for="medication_name" :value="__('Medication')" />
                            <x-text-input id="medication_name" name="medication_name" type="text" class="block mt-1 w-full" :value="old('medication_name')" />
                            <x-input-error :messages="$errors->get('medication_name')" class="mt-2" />
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            <div>
                                <x-input-label for="dosage" :value="__('Dosage')" />
                                <x-text-input id="dosage" name="dosage" type="text" class="block mt-1 w-full" :value="old('dosage')" placeholder="5 mg" />
                                <x-input-error :messages="$errors->get('dosage')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="frequency" :value="__('Frequency')" />
                                <x-text-input id="frequency" name="frequency" type="text" class="block mt-1 w-full" :value="old('frequency')" placeholder="Once daily" />
                                <x-input-error :messages="$errors->get('frequency')" class="mt-2" />
                            </div>
                            <div>
                                <x-input-label for="duration_days" :value="__('Duration (days)')" />
                                <x-text-input id="duration_days" name="duration_days" type="number" min="1" class="block mt-1 w-full" :value="old('duration_days')" placeholder="30" />
                                <x-input-error :messages="$errors->get('duration_days')" class="mt-2" />
                            </div>
                        </div>
                        <div>
                            <x-input-label for="instructions" :value="__('Instructions (optional)')" />
                            <x-text-input id="instructions" name="instructions" type="text" class="block mt-1 w-full" :value="old('instructions')" placeholder="Take in the morning with water" />
                            <x-input-error :messages="$errors->get('instructions')" class="mt-2" />
                        </div>
                        <div class="flex justify-end">
                            <x-secondary-button type="submit">Add medication</x-secondary-button>
                        </div>
                    </form>
                </div>
            @endif
        </section>
    </div>
</x-app-layout>
