<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium tracking-wide text-gray-500">Your care</p>
                <h1 class="mt-1 text-3xl font-semibold text-gray-900">My appointments</h1>
                <p class="mt-2 text-gray-600 max-w-xl">
                    Your upcoming and past visits. You can cancel an upcoming one any time;
                    the time then opens up for others.
                </p>
            </div>

            <a href="{{ route('patient.booking.doctors') }}"
               class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                Book an appointment
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="{ cancelAction: '', cancelLabel: '' }">

        @if (session('status'))
            <div class="mb-6 rounded-xl bg-teal-50 border border-teal-200 px-4 py-3 text-sm text-teal-800">
                {{ session('status') }}
            </div>
        @endif

        @if ($upcoming->isEmpty() && $past->isEmpty())
            {{-- Designed empty state. --}}
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white/60 px-8 py-16 text-center">
                <h2 class="text-lg font-semibold text-gray-900">No appointments yet</h2>
                <p class="mt-2 text-gray-600">
                    Find a doctor to book your first.
                </p>
                <a href="{{ route('patient.booking.doctors') }}"
                   class="mt-6 inline-flex items-center justify-center rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                    Find a doctor
                </a>
            </div>
        @else
            @if ($upcoming->isNotEmpty())
                <section class="mb-10">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Upcoming</h2>
                    <div class="mt-3 space-y-3">
                        @foreach ($upcoming as $appointment)
                            @include('patient.appointments.partials.card', ['appointment' => $appointment, 'cancellable' => $appointment->status->name === 'scheduled'])
                        @endforeach
                    </div>
                </section>
            @endif

            @if ($past->isNotEmpty())
                <section>
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Past</h2>
                    <div class="mt-3 space-y-3">
                        @foreach ($past as $appointment)
                            @include('patient.appointments.partials.card', ['appointment' => $appointment, 'cancellable' => false])
                        @endforeach
                    </div>
                </section>
            @endif
        @endif

        {{-- Shared confirm-before-cancel modal; target set per appointment. --}}
        <x-modal name="confirm-cancel" focusable>
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900">Cancel this appointment?</h2>
                <p class="mt-2 text-sm text-gray-600">
                    <span x-text="cancelLabel" class="font-medium text-gray-900"></span>
                    will be cancelled and the time will open up for someone else. This cannot be undone.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Keep it
                    </x-secondary-button>

                    <form method="POST" x-bind:action="cancelAction">
                        @csrf
                        @method('DELETE')
                        <x-danger-button type="submit">
                            Cancel appointment
                        </x-danger-button>
                    </form>
                </div>
            </div>
        </x-modal>
    </div>
</x-app-layout>
