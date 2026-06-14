<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-gray-500">Book an appointment</p>
        <h1 class="mt-1 text-3xl font-semibold text-gray-900">
            Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
        </h1>
        <p class="mt-2 text-gray-600">
            {{ $doctor->specialization->display_name }}
        </p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('patient.booking.doctors') }}"
               class="text-sm font-medium text-gray-500 transition hover:text-gray-900">
                &larr; All doctors
            </a>
        </div>

        @if (session('error'))
            <div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                {{ $errors->first() }}
            </div>
        @endif

        @if ($slotsByDate->isEmpty())
            {{-- Warm empty state. --}}
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white/60 px-8 py-16 text-center">
                <h2 class="text-lg font-semibold text-gray-900">No open slots right now</h2>
                <p class="mt-2 text-gray-600">
                    Try another doctor or check back soon &mdash; new times open up regularly.
                </p>
                <a href="{{ route('patient.booking.doctors') }}"
                   class="mt-6 inline-flex items-center justify-center rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                    Browse other doctors
                </a>
            </div>
        @else
            <div x-data="{
                    selected: null,
                    selectedLabel: '',
                    action: '',
                    select(id, label, action) {
                        this.selected = id;
                        this.selectedLabel = label;
                        this.action = action;
                    }
                 }">
                <p class="text-gray-700 mb-4">Pick a time that works for you.</p>

                @foreach ($slotsByDate as $date => $slots)
                    <section class="mb-6">
                        <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                            {{ \Illuminate\Support\Carbon::parse($date)->format('l, F j, Y') }}
                        </h2>

                        <div class="mt-3 flex flex-wrap gap-2">
                            @foreach ($slots as $slot)
                                @php
                                    $start = \Illuminate\Support\Carbon::parse($slot->slot_start)->format('g:i A');
                                    $end = \Illuminate\Support\Carbon::parse($slot->slot_end)->format('g:i A');
                                    $label = \Illuminate\Support\Carbon::parse($date)->format('D, M j').' at '.$start;
                                @endphp
                                <button type="button"
                                        @click="select('{{ $slot->id }}', @js($label), '{{ route('patient.booking.store', [$doctor, $slot]) }}')"
                                        :class="selected === '{{ $slot->id }}'
                                            ? 'border-teal-600 bg-teal-50 text-teal-800 ring-2 ring-teal-600'
                                            : 'border-gray-200 bg-white text-gray-700 hover:border-teal-300'"
                                        class="rounded-xl border px-4 py-2.5 text-sm font-medium tabular-nums transition focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600">
                                    {{ $start }} &ndash; {{ $end }}
                                </button>
                            @endforeach
                        </div>
                    </section>
                @endforeach

                {{-- Confirm panel: appears once a slot is chosen. One primary action. --}}
                <div x-show="selected" x-cloak x-transition
                     class="mt-6 rounded-2xl bg-white border border-gray-200/80 p-6">
                    <form method="POST" x-bind:action="action">
                        @csrf
                        <p class="text-gray-700">
                            You&rsquo;re booking
                            <span class="font-medium text-gray-900" x-text="selectedLabel"></span>
                            with Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}.
                        </p>

                        <div class="mt-4">
                            <x-input-label for="reason" :value="__('Reason for visit (optional)')" />
                            <textarea id="reason" name="reason" rows="3"
                                      class="block mt-1 w-full rounded-md border-gray-300 shadow-sm focus:border-teal-500 focus:ring-teal-500"
                                      placeholder="Briefly, what would you like to be seen for?">{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <div class="mt-5 flex items-center justify-end gap-3">
                            <button type="button" @click="selected = null"
                                    class="text-sm font-medium text-gray-500 transition hover:text-gray-900">
                                Choose another time
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-6 py-3 text-base font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                                Confirm booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
