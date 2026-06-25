<x-app-layout>
    <x-slot name="header">
        <div class="max-w-3xl mx-auto">
            <p class="text-sm font-medium tracking-wide text-gray-500">Book an appointment</p>
            <h1 class="mt-1 text-3xl font-semibold text-gray-900">Find a doctor</h1>
            <p class="mt-2 text-gray-600 max-w-xl">
                Choose a doctor to see their open times. The number beside each one is how
                many openings they have coming up.
            </p>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <form method="GET" action="{{ route('patient.booking.doctors') }}" class="mb-6">
            <label for="q" class="sr-only">Search by name or specialization</label>
            <div class="flex flex-col sm:flex-row gap-3">
                <x-text-input id="q" name="q" type="search"
                              class="block w-full"
                              :value="$search"
                              placeholder="Search by name or specialization" />
                <x-primary-button class="shrink-0 w-full sm:w-auto justify-center">
                    Search
                </x-primary-button>
            </div>
        </form>

        @forelse ($doctors as $doctor)
            <a href="{{ route('patient.booking.availability', $doctor) }}"
               aria-label="Book appointment with Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}, {{ $doctor->specialization->display_name }}. {{ $doctor->open_slots_count > 0 ? $doctor->open_slots_count . ' openings available.' : 'No openings yet.' }}"
               class="group flex items-center justify-between gap-4 rounded-2xl bg-white border border-gray-200/80 px-6 py-5 mb-3 transition-all duration-200 hover:-translate-y-0.5 hover:border-primary-light hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                <div>
                    <div class="text-lg font-medium text-gray-900">
                        Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
                    </div>
                    <div class="mt-0.5 text-sm text-gray-500">
                        {{ $doctor->specialization->display_name }}
                    </div>
                </div>

                <div class="text-right">
                    @if ($doctor->open_slots_count > 0)
                        <div class="text-sm font-medium text-primary">
                            {{ $doctor->open_slots_count }} {{ Str::plural('opening', $doctor->open_slots_count) }}
                        </div>
                        <div class="mt-0.5 text-sm text-gray-500 group-hover:text-gray-600">View availability</div>
                    @else
                        <div class="text-sm text-gray-500">No openings yet</div>
                    @endif
                </div>
            </a>
        @empty
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white/60 px-8 py-16 text-center">
                <h2 class="text-lg font-semibold text-gray-900">No doctors found</h2>
                <p class="mt-2 text-gray-600">
                    @if ($search !== '')
                        Nothing matched &ldquo;{{ $search }}&rdquo;. Try a different name or specialization.
                    @else
                        There are no doctors to show right now. Please check back soon.
                    @endif
                </p>
                @if ($search !== '')
                    <a href="{{ route('patient.booking.doctors') }}"
                       class="mt-6 inline-flex items-center text-sm font-medium text-primary hover:text-primary-dark">
                        Clear search
                    </a>
                @endif
            </div>
        @endforelse
    </div>
</x-app-layout>
