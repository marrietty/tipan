<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium tracking-wide text-gray-500">Accounts</p>
                <h1 class="mt-1 text-3xl font-semibold text-gray-900">Doctors</h1>
                <p class="mt-2 text-gray-600 max-w-xl">
                    Every doctor account, their specialization, and how much availability they have open.
                </p>
            </div>

            <a href="{{ route('admin.doctors.create') }}"
               class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                Add a doctor
            </a>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        @if (session('status'))
            <div class="mb-6 rounded-xl bg-teal-50 border border-teal-200 px-4 py-3 text-sm text-teal-800">
                {{ session('status') }}
            </div>
        @endif

        @forelse ($doctors as $doctor)
            <div class="flex items-center justify-between gap-4 rounded-2xl bg-white border border-gray-200/80 px-6 py-5 mb-3">
                <div>
                    <div class="text-lg font-medium text-gray-900">
                        Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
                    </div>
                    <div class="mt-0.5 text-sm text-gray-500">
                        {{ $doctor->specialization->display_name }}
                        <span class="text-gray-300">&middot;</span>
                        License {{ $doctor->license_number }}
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-sm font-medium text-gray-900 tabular-nums">{{ $doctor->open_slots_count }}</div>
                    <div class="text-xs text-gray-500">open {{ Str::plural('slot', $doctor->open_slots_count) }}</div>
                </div>
            </div>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white/60 px-8 py-16 text-center">
                <h2 class="text-lg font-semibold text-gray-900">No doctors yet</h2>
                <p class="mt-2 text-gray-600">Add your first doctor account to get started.</p>
                <a href="{{ route('admin.doctors.create') }}"
                   class="mt-6 inline-flex items-center justify-center rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                    Add a doctor
                </a>
            </div>
        @endforelse
    </div>
</x-app-layout>
