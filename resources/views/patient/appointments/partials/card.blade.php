@php
    $statusStyles = [
        'scheduled' => 'bg-teal-50 text-teal-700',
        'completed' => 'bg-gray-100 text-gray-600',
        'missed' => 'bg-amber-50 text-amber-700',
    ];
    $start = \Illuminate\Support\Carbon::parse($appointment->schedule->slot_start)->format('g:i A');
    $end = \Illuminate\Support\Carbon::parse($appointment->schedule->slot_end)->format('g:i A');
    $date = $appointment->schedule->available_date->format('l, F j, Y');
    $label = $appointment->schedule->available_date->format('D, M j').' at '.$start;
@endphp

<div class="rounded-2xl bg-white border border-gray-200/80 px-6 py-5">
    <div class="flex items-start justify-between gap-4">
        <div>
            <div class="text-lg font-medium text-gray-900">
                Dr. {{ $appointment->doctor->first_name }} {{ $appointment->doctor->last_name }}
            </div>
            <div class="mt-0.5 text-sm text-gray-500">
                {{ $appointment->doctor->specialization->display_name }}
            </div>
            <div class="mt-3 text-sm text-gray-900">
                {{ $date }}
                <span class="text-gray-500 tabular-nums">&middot; {{ $start }} &ndash; {{ $end }}</span>
            </div>
            @if ($appointment->reason)
                <div class="mt-1 text-sm text-gray-500">
                    {{ $appointment->reason }}
                </div>
            @endif
        </div>

        <span class="shrink-0 inline-flex items-center rounded-full px-3 py-1 text-xs font-medium {{ $statusStyles[$appointment->status->name] ?? 'bg-gray-100 text-gray-600' }}">
            {{ $appointment->status->display_name }}
        </span>
    </div>

    @if ($cancellable)
        <div class="mt-4 flex justify-end border-t border-gray-100 pt-4">
            {{-- Cancellation is as visible as booking; no dark patterns. --}}
            <button type="button"
                    @click="cancelAction = '{{ route('patient.appointments.destroy', $appointment) }}'; cancelLabel = @js('Your visit with Dr. '.$appointment->doctor->last_name.' on '.$label); $dispatch('open-modal', 'confirm-cancel')"
                    class="text-sm font-medium text-gray-500 transition hover:text-red-700 focus:outline-none focus-visible:underline">
                Cancel appointment
            </button>
        </div>
    @endif
</div>
