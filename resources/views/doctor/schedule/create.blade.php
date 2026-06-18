<x-app-layout>
    <x-slot name="header">
        <p class="text-sm font-medium tracking-wide text-primary">Availability</p>
        <h1 class="mt-1 text-3xl font-semibold text-heading">Add availability</h1>
        <p class="mt-2 text-body max-w-xl">
            Pick a date and a time window, then choose how long each appointment should be.
            We'll open a bookable slot for every duration that fits the window.
        </p>
    </x-slot>

    @php
        // Render this month plus the next two so the doctor can schedule ahead,
        // without paging to arbitrary far-future months.
        $today = \Illuminate\Support\Carbon::today();
        $calendars = [];
        for ($m = 0; $m < 3; $m++) {
            $first = $today->copy()->startOfMonth()->addMonths($m);
            $leading = (int) $first->dayOfWeek; // 0 = Sunday
            $cells = array_fill(0, $leading, null);
            for ($d = 1; $d <= $first->daysInMonth; $d++) {
                $cells[] = $first->copy()->day($d)->toDateString();
            }
            $calendars[] = ['label' => $first->format('F Y'), 'cells' => $cells];
        }

        // Half-hour options for the time selects, formatted for display.
        $times = [];
        for ($minutes = 0; $minutes < 24 * 60; $minutes += 30) {
            $value = sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);
            $times[$value] = \Illuminate\Support\Carbon::createFromTimeString($value)->format('g:i A');
        }
    @endphp

    <div class="max-w-lg mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="rounded-2xl bg-white border border-line p-8 shadow-sm">
            <form method="POST" action="{{ route('doctor.schedule.store') }}" class="space-y-6"
                  x-data="{
                      monthIndex: 0,
                      months: {{ Illuminate\Support\Js::from(array_column($calendars, 'label')) }},
                      today: {{ Illuminate\Support\Js::from($today->toDateString()) }},
                      selectedDate: {{ Illuminate\Support\Js::from(old('available_date', '')) }},
                      start: {{ Illuminate\Support\Js::from(old('slot_start', '')) }},
                      end: {{ Illuminate\Support\Js::from(old('slot_end', '')) }},
                      duration: {{ Illuminate\Support\Js::from((int) old('duration', 30)) }},
                      isPast(date) { return date !== null && date < this.today; },
                      toMinutes(t) {
                          if (!t) return null;
                          const [h, m] = t.split(':').map(Number);
                          return h * 60 + m;
                      },
                      get slotCount() {
                          const s = this.toMinutes(this.start), e = this.toMinutes(this.end);
                          if (s === null || e === null || e <= s) return 0;
                          return Math.floor((e - s) / this.duration);
                      },
                      formatDate(date) {
                          return date
                              ? new Date(date + 'T00:00:00').toLocaleDateString(undefined, { weekday: 'long', month: 'long', day: 'numeric', year: 'numeric' })
                              : '';
                      }
                  }">
                @csrf

                {{-- Date: calendar picker feeding a hidden input. --}}
                <div>
                    <x-input-label :value="__('Date')" />
                    <input type="hidden" name="available_date" x-model="selectedDate" />

                    <div class="mt-1 rounded-xl border border-line p-4">
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
                                        @php $day = \Illuminate\Support\Carbon::parse($date)->day; @endphp
                                        <button type="button"
                                                @click="selectedDate = '{{ $date }}'"
                                                :disabled="isPast('{{ $date }}')"
                                                :class="selectedDate === '{{ $date }}'
                                                    ? 'bg-primary text-white font-semibold'
                                                    : (isPast('{{ $date }}')
                                                        ? 'text-muted cursor-not-allowed'
                                                        : 'text-heading hover:bg-primary-indigo/40 font-medium')"
                                                class="aspect-square rounded-lg text-sm transition focus:outline-none focus-visible:ring-2 focus-visible:ring-primary">
                                            {{ $day }}
                                        </button>
                                    @endif
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <p class="mt-2 text-sm text-body" x-show="selectedDate" x-cloak>
                        Selected: <span class="font-medium text-heading" x-text="formatDate(selectedDate)"></span>
                    </p>
                    <x-input-error :messages="$errors->get('available_date')" class="mt-2" />
                </div>

                {{-- Time window. --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="slot_start" :value="__('From')" />
                        <select id="slot_start" name="slot_start" required x-model="start"
                                class="block mt-1 w-full rounded-lg border-line shadow-sm focus:border-primary focus:ring-primary">
                            <option value="" disabled>Select start time</option>
                            @foreach ($times as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('slot_start')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="slot_end" :value="__('To')" />
                        <select id="slot_end" name="slot_end" required x-model="end"
                                class="block mt-1 w-full rounded-lg border-line shadow-sm focus:border-primary focus:ring-primary">
                            <option value="" disabled>Select end time</option>
                            @foreach ($times as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('slot_end')" class="mt-2" />
                    </div>
                </div>

                {{-- Per-slot duration. --}}
                <div>
                    <x-input-label for="duration" :value="__('Appointment length')" />
                    <select id="duration" name="duration" required x-model.number="duration"
                            class="block mt-1 w-full rounded-lg border-line shadow-sm focus:border-primary focus:ring-primary">
                        <option value="15">15 minutes</option>
                        <option value="30">30 minutes</option>
                        <option value="45">45 minutes</option>
                        <option value="60">1 hour</option>
                    </select>
                    <x-input-error :messages="$errors->get('duration')" class="mt-2" />
                </div>

                {{-- Live preview of how many slots this will create. --}}
                <div x-show="slotCount > 0" x-cloak
                     class="rounded-xl bg-primary-indigo/30 border border-primary-indigo px-4 py-3 text-sm text-primary-dark">
                    This will open
                    <span class="font-semibold" x-text="slotCount"></span>
                    <span x-text="slotCount === 1 ? 'bookable slot' : 'bookable slots'"></span>
                    of <span class="font-semibold" x-text="duration"></span> minutes each.
                </div>
                <div x-show="start && end && slotCount === 0" x-cloak
                     class="rounded-xl bg-warning/10 border border-warning/30 px-4 py-3 text-sm text-warning">
                    That window is shorter than one appointment. Widen it or pick a shorter length.
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <a href="{{ route('doctor.schedule.index') }}"
                       class="text-sm font-medium text-body transition hover:text-heading">
                        Cancel
                    </a>

                    {{-- Primary action. --}}
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-xl bg-primary px-5 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-primary-dark focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2">
                        Add slots
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
