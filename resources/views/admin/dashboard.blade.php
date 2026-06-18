<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium tracking-wide text-primary">Admin</p>
                <h1 class="mt-1 text-3xl font-semibold text-heading">
                    Hello, {{ Auth::user()->displayName() }}.
                </h1>
                <p class="mt-2 text-body max-w-xl">
                    An overview of the clinic: accounts and activity at a glance.
                </p>
            </div>

            {{-- One clear primary action. --}}
            <a href="{{ route('admin.doctors.create') }}"
               class="inline-flex items-center justify-center rounded-xl bg-primary px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                Add a doctor
            </a>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        {{-- Accounts --}}
        <div class="grid gap-5 sm:grid-cols-2">
            <a href="{{ route('admin.patients.index') }}"
               class="rounded-2xl bg-white border border-line p-7 transition hover:border-primary-light hover:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                <div class="text-sm text-body">Patients</div>
                <div class="mt-2 text-4xl font-semibold text-heading tabular-nums">{{ $patientCount }}</div>
            </a>
            <a href="{{ route('admin.doctors.index') }}"
               class="rounded-2xl bg-white border border-line p-7 transition hover:border-primary-light hover:shadow-sm focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                <div class="text-sm text-body">Doctors</div>
                <div class="mt-2 text-4xl font-semibold text-heading tabular-nums">{{ $doctorCount }}</div>
            </a>
        </div>

        {{-- Appointments by status --}}
        <div class="mt-10">
            <div class="flex items-baseline justify-between">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-body">Appointments by status</h2>
                <a href="{{ route('admin.appointments.index') }}" class="text-sm font-medium text-primary hover:text-primary-dark">
                    View all
                </a>
            </div>

            @if ($appointmentTotal === 0)
                <div class="mt-3 rounded-2xl border border-dashed border-line bg-white/60 px-8 py-12 text-center text-body">
                    No appointments in the system yet.
                </div>
            @else
                <div class="mt-3 grid gap-5 sm:grid-cols-3">
                    @foreach ($countsByStatus as $label => $total)
                        <div class="rounded-2xl bg-white border border-line p-7 shadow-sm">
                            <div class="text-sm text-body">{{ $label }}</div>
                            <div class="mt-2 text-4xl font-semibold text-heading tabular-nums">{{ $total }}</div>
                        </div>
                    @endforeach
                </div>
                <p class="mt-3 text-sm text-muted">
                    Cancelled appointments are removed when cancelled, so they are not counted here.
                </p>
            @endif
        </div>
    </div>
</x-app-layout>
