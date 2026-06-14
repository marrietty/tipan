<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium tracking-wide text-gray-500">Admin</p>
                <h1 class="mt-1 text-3xl font-semibold text-gray-900">
                    Hello, {{ Auth::user()->displayName() }}.
                </h1>
                <p class="mt-2 text-gray-600 max-w-xl">
                    An overview of the clinic &mdash; accounts and activity at a glance.
                </p>
            </div>

            {{-- One clear primary action. --}}
            <a href="{{ route('admin.doctors.create') }}"
               class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                Add a doctor
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Accounts --}}
        <div class="grid gap-5 sm:grid-cols-2">
            <a href="{{ route('admin.patients.index') }}"
               class="rounded-2xl bg-white border border-gray-200/80 p-7 transition hover:border-teal-300 hover:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600">
                <div class="text-sm text-gray-500">Patients</div>
                <div class="mt-2 text-4xl font-semibold text-gray-900 tabular-nums">{{ $patientCount }}</div>
            </a>
            <a href="{{ route('admin.doctors.index') }}"
               class="rounded-2xl bg-white border border-gray-200/80 p-7 transition hover:border-teal-300 hover:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600">
                <div class="text-sm text-gray-500">Doctors</div>
                <div class="mt-2 text-4xl font-semibold text-gray-900 tabular-nums">{{ $doctorCount }}</div>
            </a>
        </div>

        {{-- Appointments by status --}}
        <div class="mt-10">
            <div class="flex items-baseline justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">Appointments by status</h2>
                <a href="{{ route('admin.appointments.index') }}" class="text-sm font-medium text-teal-700 hover:text-teal-800">
                    View all
                </a>
            </div>

            @if ($appointmentTotal === 0)
                <div class="mt-3 rounded-2xl border border-dashed border-gray-300 bg-white/60 px-8 py-12 text-center text-gray-600">
                    No appointments in the system yet.
                </div>
            @else
                <div class="mt-3 grid gap-5 sm:grid-cols-3">
                    @foreach ($countsByStatus as $label => $total)
                        <div class="rounded-2xl bg-white border border-gray-200/80 p-7">
                            <div class="text-sm text-gray-500">{{ $label }}</div>
                            <div class="mt-2 text-4xl font-semibold text-gray-900 tabular-nums">{{ $total }}</div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-sm text-gray-400">
                    Cancelled appointments are removed when cancelled, so they are not counted here.
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
