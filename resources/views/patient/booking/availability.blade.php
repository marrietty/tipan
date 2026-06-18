<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-primary">Book an appointment</p>
        <h1 class="mt-1 text-3xl font-semibold text-heading">
            Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}
        </h1>
        <p class="mt-2 text-body">
            {{ $doctor->specialization->display_name }}
        </p>
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('patient.booking.doctors') }}"
               class="inline-flex items-center gap-1.5 text-sm font-medium text-body transition hover:text-heading">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                All doctors
            </a>
        </div>

        @if (session('error'))
            <div class="mb-6 rounded-xl bg-warning/10 border border-warning/30 px-4 py-3 text-sm text-warning">
                {{ session('error') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-warning/10 border border-warning/30 px-4 py-3 text-sm text-warning">
                {{ $errors->first() }}
            </div>
        @endif

        @if ($slotsByDate->isEmpty())
            {{-- Warm empty state. --}}
            <div class="rounded-2xl border border-dashed border-line bg-white/60 px-8 py-16 text-center">
                <h2 class="text-lg font-semibold text-heading">No open slots right now</h2>
                <p class="mt-2 text-body">
                    Try another doctor or check back soon. New times open up regularly.
                </p>
                <a href="{{ route('patient.booking.doctors') }}"
                   class="mt-6 inline-flex items-center justify-center rounded-xl bg-primary px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                    Browse other doctors
                </a>
            </div>
        @else
            @php
                // Build a date-keyed map of selectable times for the picker, and the
                // ordered list of months that actually contain availability so the
                // calendar only ever lands on a month with open slots.
                $slotMap = [];
                foreach ($slotsByDate as $date => $slots) {
                    $slotMap[$date] = $slots->map(fn ($slot) => [
                        'start'  => \Illuminate\Support\Carbon::parse($slot->slot_start)->format('g:i A'),
                        'end'    => \Illuminate\Support\Carbon::parse($slot->slot_end)->format('g:i A'),
                        'action' => route('patient.booking.store', [$doctor, $slot]),
                    ])->values();
                }

                $availableDates = array_keys($slotMap);
                $months = collect($availableDates)
                    ->map(fn ($d) => \Illuminate\Support\Carbon::parse($d)->startOfMonth()->toDateString())
                    ->unique()
                    ->values()
                    ->all();

                // Pre-render each month's calendar grid (leading/trailing blanks padded).
                $calendars = [];
                foreach ($months as $monthStart) {
                    $first = \Illuminate\Support\Carbon::parse($monthStart);
                    $daysInMonth = $first->daysInMonth;
                    $leading = (int) $first->dayOfWeek; // 0 = Sunday
                    $cells = array_fill(0, $leading, null);
                    for ($d = 1; $d <= $daysInMonth; $d++) {
                        $cells[] = $first->copy()->day($d)->toDateString();
                    }
                    $calendars[] = [
                        'label' => $first->format('F Y'),
                        'cells' => $cells,
                    ];
                }
            @endphp

            <div x-data="{
                    monthIndex: 0,
                    months: {{ Illuminate\Support\Js::from(array_column($calendars, 'label')) }},
                    slots: {{ Illuminate\Support\Js::from($slotMap) }},
                    today: {{ Illuminate\Support\Js::from(\Illuminate\Support\Carbon::today()->toDateString()) }},
                    selectedDate: null,
                    selectedSlot: null,
                    selectedLabel: '',
                    action: '',
                    isAvailable(date) { return date !== null && this.slots[date] !== undefined; },
                    pickDate(date) {
                        if (!this.isAvailable(date)) return;
                        this.selectedDate = date;
                        this.selectedSlot = null;
                        this.action = '';
                    },
                    pickSlot(date, index) {
                        const s = this.slots[date][index];
                        this.selectedSlot = date + '-' + index;
                        this.action = s.action;
                        this.selectedLabel = this.formatDate(date) + ' at ' + s.start;
                    },
                    formatDate(date) {
                        return new Date(date + 'T00:00:00').toLocaleDateString(undefined, {
                            weekday: 'short', month: 'short', day: 'numeric'
                        });
                    }
                 }">
                <p class="text-body mb-4">Pick a date, then choose a time that works for you.</p>

                {{-- Calendar --}}
                <div class="rounded-2xl bg-white border border-line p-5 shadow-sm">
                    {{-- Month switcher --}}
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

                    {{-- Weekday headings --}}
                    <div class="mt-4 grid grid-cols-7 gap-1 text-center text-xs font-medium text-muted">
                        @foreach (['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dow)
                            <div class="py-1">{{ $dow }}</div>
                        @endforeach
                    </div>

                    {{-- One grid per month; only the active month shows. --}}
                    @foreach ($calendars as $mi => $calendar)
                        <div x-show="monthIndex === {{ $mi }}" class="mt-1 grid grid-cols-7 gap-1">
                            @foreach ($calendar['cells'] as $date)
                                @if ($date === null)
                                    <div></div>
                                @else
                                    @php $day = \Illuminate\Support\Carbon::parse($date)->day; @endphp
                                    <button type="button"
                                            @click="pickDate('{{ $date }}')"
                                            :disabled="!isAvailable('{{ $date }}')"
                                            :class="selectedDate === '{{ $date }}'
                                                ? 'bg-primary text-white font-semibold'
                                                : (isAvailable('{{ $date }}')
                                                    ? 'text-heading hover:bg-primary-indigo/40 font-medium'
                                                    : 'text-muted cursor-not-allowed')"
                                            class="relative aspect-square rounded-lg text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                                        {{ $day }}
                                        <template x-if="isAvailable('{{ $date }}') && selectedDate !== '{{ $date }}'">
                                            <span class="absolute bottom-1 left-1/2 -translate-x-1/2 h-1 w-1 rounded-full bg-primary"></span>
                                        </template>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                    @endforeach
                </div>

                {{-- Time picker: appears once a date is chosen. --}}
                <div x-show="selectedDate" x-cloak x-transition
                     class="mt-6 rounded-2xl bg-white border border-line p-5 shadow-sm">
                    <h3 class="text-sm font-semibold uppercase tracking-wide text-body">
                        Available times
                        <span class="text-primary" x-text="selectedDate ? formatDate(selectedDate) : ''"></span>
                    </h3>

                    <div class="mt-3 flex flex-wrap gap-2">
                        <template x-for="(slot, index) in (selectedDate ? slots[selectedDate] : [])" :key="index">
                            <button type="button"
                                    @click="pickSlot(selectedDate, index)"
                                    :class="selectedSlot === (selectedDate + '-' + index)
                                        ? 'border-primary bg-primary-indigo/30 text-primary-dark ring-2 ring-primary'
                                        : 'border-line bg-white text-body hover:border-primary-light'"
                                    class="rounded-xl border px-4 py-2.5 text-sm font-medium tabular-nums transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                                <span x-text="slot.start"></span> &ndash; <span x-text="slot.end"></span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- Confirm panel: appears once a time is chosen. One primary action. --}}
                <div x-show="selectedSlot" x-cloak x-transition
                     class="mt-6 rounded-2xl bg-white border border-line p-6 shadow-sm">
                    <form method="POST" x-bind:action="action">
                        @csrf
                        <p class="text-body">
                            You&rsquo;re booking
                            <span class="font-medium text-heading" x-text="selectedLabel"></span>
                            with Dr. {{ $doctor->first_name }} {{ $doctor->last_name }}.
                        </p>

                        <div class="mt-4">
                            <x-input-label for="reason" :value="__('Reason for visit (optional)')" />
                            <textarea id="reason" name="reason" rows="3"
                                      class="block mt-1 w-full rounded-md border-line shadow-sm focus:border-primary focus:ring-primary"
                                      placeholder="Briefly, what would you like to be seen for?">{{ old('reason') }}</textarea>
                            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
                        </div>

                        <div class="mt-5 flex items-center justify-end gap-3">
                            <button type="button" @click="selectedSlot = null; action = ''"
                                    class="text-sm font-medium text-body transition hover:text-heading">
                                Choose another time
                            </button>
                            <button type="submit"
                                    class="inline-flex items-center justify-center rounded-xl bg-primary px-6 py-3 text-base font-medium text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                                Confirm booking
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
