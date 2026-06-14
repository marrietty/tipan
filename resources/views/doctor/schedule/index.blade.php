<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium tracking-wide text-gray-500">Availability</p>
                <h1 class="mt-1 text-3xl font-semibold text-gray-900">Your schedule</h1>
                <p class="mt-2 text-gray-600 max-w-xl">
                    These are the times patients can book with you. Open as many as you like;
                    booked times are kept until the visit is done.
                </p>
            </div>

            {{-- One primary action. --}}
            <a href="{{ route('doctor.schedule.create') }}"
               class="inline-flex items-center justify-center rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                Add a slot
            </a>
        </div>
    </x-slot>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="{ confirmDelete: null, confirmLabel: '' }">

        {{-- Flash messages: honest, immediate state. --}}
        @if (session('status'))
            <div class="mb-6 rounded-xl bg-teal-50 border border-teal-200 px-4 py-3 text-sm text-teal-800">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-xl bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                {{ session('error') }}
            </div>
        @endif

        @forelse ($slotsByDate as $date => $slots)
            <section class="mb-8">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500">
                    {{ \Illuminate\Support\Carbon::parse($date)->format('l, F j, Y') }}
                </h2>

                <ul class="mt-3 divide-y divide-gray-100 rounded-2xl bg-white border border-gray-200/80">
                    @foreach ($slots as $slot)
                        @php
                            $start = \Illuminate\Support\Carbon::parse($slot->slot_start)->format('g:i A');
                            $end = \Illuminate\Support\Carbon::parse($slot->slot_end)->format('g:i A');
                        @endphp
                        <li class="flex items-center justify-between gap-4 px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="text-base font-medium text-gray-900 tabular-nums">
                                    {{ $start }} &ndash; {{ $end }}
                                </span>

                                @if ($slot->is_booked)
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600">
                                        Booked
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-teal-50 px-2.5 py-0.5 text-xs font-medium text-teal-700">
                                        Open
                                    </span>
                                @endif
                            </div>

                            @if ($slot->is_booked)
                                <span class="text-xs text-gray-400">Kept until the visit is done</span>
                            @else
                                <button type="button"
                                        @click="confirmDelete = '{{ route('doctor.schedule.destroy', $slot) }}'; confirmLabel = '{{ \Illuminate\Support\Carbon::parse($date)->format('M j') }}, {{ $start }}–{{ $end }}'; $dispatch('open-modal', 'confirm-slot-delete')"
                                        class="text-sm font-medium text-gray-500 transition hover:text-red-700 focus:outline-none focus-visible:underline">
                                    Remove
                                </button>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            {{-- Designed empty state, not a blank table. --}}
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white/60 px-8 py-16 text-center">
                <h2 class="text-lg font-semibold text-gray-900">No availability set</h2>
                <p class="mt-2 text-gray-600">
                    Add your first slot and patients will be able to book it right away.
                </p>
                <a href="{{ route('doctor.schedule.create') }}"
                   class="mt-6 inline-flex items-center justify-center rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-teal-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-teal-600 focus-visible:ring-offset-2">
                    Add your first slot
                </a>
            </div>
        @endforelse

        {{-- Shared confirm-before-delete modal; the target slot is set per row. --}}
        <x-modal name="confirm-slot-delete" focusable>
            <div class="p-6">
                <h2 class="text-lg font-semibold text-gray-900">Remove this slot?</h2>
                <p class="mt-2 text-sm text-gray-600">
                    <span x-text="confirmLabel" class="font-medium text-gray-900"></span>
                    will no longer be available to book. This cannot be undone.
                </p>

                <div class="mt-6 flex justify-end gap-3">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Keep it
                    </x-secondary-button>

                    <form method="POST" x-bind:action="confirmDelete">
                        @csrf
                        @method('DELETE')
                        <x-danger-button type="submit">
                            Remove slot
                        </x-danger-button>
                    </form>
                </div>
            </div>
        </x-modal>
    </div>
</x-app-layout>
