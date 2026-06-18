<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-sm font-medium tracking-wide text-primary">Availability</p>
                <h1 class="mt-1 text-3xl font-semibold text-heading">Your schedule</h1>
                <p class="mt-2 text-body max-w-xl">
                    These are the times patients can book with you. Open as many as you like;
                    booked times are kept until the visit is done.
                </p>
            </div>

            {{-- One primary action. --}}
            <a href="{{ route('doctor.schedule.create') }}"
               class="inline-flex items-center justify-center rounded-xl bg-primary px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                Add a slot
            </a>
        </div>
    </x-slot>

    @php
        // Per-date slot tallies, surfaced on the calendar as open/total counts.
        //   total  = slots that day
        //   booked = slots already taken
        //   open   = bookable slots remaining
        $dateCounts = [];
        foreach ($slotsByDate as $date => $slots) {
            $total = $slots->count();
            $booked = $slots->where('is_booked', true)->count();
            $dateCounts[$date] = [
                'total' => $total,
                'booked' => $booked,
                'open' => $total - $booked,
            ];
        }

        // Render only the months that actually contain slots.
        $months = collect(array_keys($dateCounts))
            ->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->startOfMonth()->toDateString())
            ->unique()->values()->all();

        $calendars = [];
        foreach ($months as $monthStart) {
            $first = \Illuminate\Support\Carbon::parse($monthStart);
            $leading = (int) $first->dayOfWeek;
            $cells = array_fill(0, $leading, null);
            for ($d = 1; $d <= $first->daysInMonth; $d++) {
                $cells[] = $first->copy()->day($d)->toDateString();
            }
            $calendars[] = ['label' => $first->format('F Y'), 'cells' => $cells];
        }
    @endphp

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8"
         x-data="{
             confirmDelete: null,
             confirmLabel: '',
             monthIndex: 0,
             selectedDay: null,
             months: {{ Illuminate\Support\Js::from(array_column($calendars, 'label')) }},
         }">

        {{-- Flash messages: honest, immediate state. --}}
        @if (session('status'))
            <div class="mb-6 rounded-xl bg-primary-indigo/30 border border-primary-indigo px-4 py-3 text-sm text-primary-dark">
                {{ session('status') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-6 rounded-xl bg-warning/10 border border-warning/30 px-4 py-3 text-sm text-warning">
                {{ session('error') }}
            </div>
        @endif

        @if ($slotsByDate->isNotEmpty())
            {{-- Mini calendar: click a day to filter the list below to that date. --}}
            <div class="mb-8 rounded-2xl bg-white border border-line p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <button type="button"
                            @click="monthIndex = Math.max(0, monthIndex - 1)"
                            :disabled="monthIndex === 0"
                            class="rounded-lg p-2 text-body transition hover:bg-canvas disabled:opacity-30 disabled:cursor-not-allowed focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <h2 class="text-sm font-semibold text-heading" x-text="months[monthIndex]"></h2>
                    <button type="button"
                            @click="monthIndex = Math.min(months.length - 1, monthIndex + 1)"
                            :disabled="monthIndex === months.length - 1"
                            class="rounded-lg p-2 text-body transition hover:bg-canvas disabled:opacity-30 disabled:cursor-not-allowed focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>

                <div class="mt-4 grid grid-cols-7 gap-1 text-center text-xs font-medium text-muted">
                    @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dow)
                        <div class="py-1">{{ $dow }}</div>
                    @endforeach
                </div>

                @foreach ($calendars as $mi => $calendar)
                    <div x-show="monthIndex === {{ $mi }}" class="mt-1 grid grid-cols-7 gap-1">
                        @foreach ($calendar['cells'] as $date)
                            @if ($date === null)
                                <div></div>
                            @else
                                @php
                                    $day = \Illuminate\Support\Carbon::parse($date)->day;
                                    $counts = $dateCounts[$date] ?? null;
                                    $isPast = \Illuminate\Support\Carbon::parse($date)->isPast()
                                        && ! \Illuminate\Support\Carbon::parse($date)->isToday();
                                @endphp
                                @if ($counts)
                                    @php
                                        $tooltip = $counts['open'].' open / '.$counts['total'].' total'
                                            .($counts['booked'] > 0 ? ', '.$counts['booked'].' booked' : '');
                                    @endphp
                                    {{-- Days with slots filter the list below when clicked.
                                         Counts surface on hover (and as a native title). --}}
                                    <button type="button"
                                            title="{{ $tooltip }}"
                                            @click="selectedDay = (selectedDay === '{{ $date }}' ? null : '{{ $date }}')"
                                            :class="selectedDay === '{{ $date }}'
                                                ? 'bg-primary text-white font-semibold'
                                                : 'text-heading hover:bg-primary-indigo/40 font-medium'"
                                            class="group relative aspect-square flex items-center justify-center rounded-lg text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                                        {{ $day }}

                                        {{-- Hover tooltip: open/total with a booked line. --}}
                                        <span class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-1 -translate-x-1/2 whitespace-nowrap rounded-lg bg-heading px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100">
                                            <span class="text-success">{{ $counts['open'] }}</span> open / {{ $counts['total'] }} total
                                            @if ($counts['booked'] > 0)
                                                <span class="block text-primary-indigo">{{ $counts['booked'] }} booked</span>
                                            @endif
                                        </span>
                                    </button>
                                @elseif ($isPast)
                                    {{-- Past day with no slots: muted, no tooltip. --}}
                                    <div class="aspect-square flex items-center justify-center rounded-lg text-sm text-muted">
                                        {{ $day }}
                                    </div>
                                @else
                                    {{-- Future day with no slots: prompt to add some on hover. --}}
                                    <div title="Slots are not yet added"
                                         class="group relative aspect-square flex items-center justify-center rounded-lg text-sm text-muted">
                                        {{ $day }}
                                        <span class="pointer-events-none absolute bottom-full left-1/2 z-20 mb-1 -translate-x-1/2 whitespace-nowrap rounded-lg bg-heading px-2.5 py-1.5 text-xs font-medium text-white opacity-0 shadow-lg transition-opacity duration-150 group-hover:opacity-100">
                                            Slots are not yet added
                                        </span>
                                    </div>
                                @endif
                            @endif
                        @endforeach
                    </div>
                @endforeach

                {{-- Legend + active-filter reset. --}}
                <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
                    <div class="text-xs text-muted">
                        Hover a day to see its slot counts. Click a day to filter the list.
                    </div>
                    <button type="button" x-show="selectedDay" x-cloak
                            @click="selectedDay = null"
                            class="text-xs font-medium text-primary transition hover:text-primary-dark focus:outline-none focus-visible:underline">
                        Show all dates
                    </button>
                </div>
            </div>
        @endif

        @forelse ($slotsByDate as $date => $slots)
            <section class="mb-8 scroll-mt-24" id="day-{{ $date }}"
                     x-show="!selectedDay || selectedDay === '{{ $date }}'">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-body">
                    {{ \Illuminate\Support\Carbon::parse($date)->format('l, F j, Y') }}
                </h2>

                <ul class="mt-3 divide-y divide-line rounded-2xl bg-white border border-line">
                    @foreach ($slots as $slot)
                        @php
                            $start = \Illuminate\Support\Carbon::parse($slot->slot_start)->format('g:i A');
                            $end = \Illuminate\Support\Carbon::parse($slot->slot_end)->format('g:i A');
                        @endphp
                        <li class="flex items-center justify-between gap-4 px-5 py-4">
                            <div class="flex items-center gap-3">
                                <span class="text-base font-medium text-heading tabular-nums">
                                    {{ $start }} &ndash; {{ $end }}
                                </span>

                                @if ($slot->is_booked)
                                    <span class="inline-flex items-center rounded-full bg-canvas px-2.5 py-0.5 text-xs font-medium text-body">
                                        Booked
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-full bg-primary-indigo/30 px-2.5 py-0.5 text-xs font-medium text-primary">
                                        Open
                                    </span>
                                @endif
                            </div>

                            @if ($slot->is_booked)
                                <span class="text-xs text-muted">Kept until the visit is done</span>
                            @else
                                <button type="button"
                                        @click="confirmDelete = '{{ route('doctor.schedule.destroy', $slot) }}'; confirmLabel = '{{ \Illuminate\Support\Carbon::parse($date)->format('M j') }}, {{ $start }}&ndash;{{ $end }}'; $dispatch('open-modal', 'confirm-slot-delete')"
                                        class="text-sm font-medium text-body transition hover:text-danger focus:outline-none focus-visible:underline">
                                    Remove
                                </button>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </section>
        @empty
            {{-- Designed empty state, not a blank table. --}}
            <div class="rounded-2xl border border-dashed border-line bg-white/60 px-8 py-16 text-center">
                <h2 class="text-lg font-semibold text-heading">No availability set</h2>
                <p class="mt-2 text-body">
                    Add your first slot and patients will be able to book it right away.
                </p>
                <a href="{{ route('doctor.schedule.create') }}"
                   class="mt-6 inline-flex items-center justify-center rounded-xl bg-primary px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                    Add your first slot
                </a>
            </div>
        @endforelse

        {{-- Shared confirm-before-delete modal; the target slot is set per row. --}}
        <x-modal name="confirm-slot-delete" focusable>
            <div class="p-6">
                <h2 class="text-lg font-semibold text-heading">Remove this slot?</h2>
                <p class="mt-2 text-sm text-body">
                    <span x-text="confirmLabel" class="font-medium text-heading"></span>
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
