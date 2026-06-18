<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;

class StoreScheduleRequest extends FormRequest
{
    /**
     * Only a doctor reaches this request (route role middleware), and slots are
     * always created for their own profile, so authorization holds here.
     */
    public function authorize(): bool
    {
        return $this->user()?->doctor !== null;
    }

    /**
     * A doctor sets a date, a time window, and a per-slot duration; the window
     * is expanded into many slots in the controller. Uniqueness is no longer a
     * field rule because the batch may legitimately overlap existing slots,
     * which are skipped rather than rejected.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'available_date' => ['required', 'date', 'after_or_equal:today'],
            'slot_start' => ['required', 'date_format:H:i'],
            'slot_end' => ['required', 'date_format:H:i', 'after:slot_start'],
            'duration' => ['required', 'integer', 'in:15,30,45,60'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'available_date.after_or_equal' => 'Choose today or a future date.',
            'slot_end.after' => 'The end time must be later than the start time.',
            'duration.in' => 'Choose a slot length of 15, 30, 45, or 60 minutes.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'available_date' => 'date',
            'slot_start' => 'start time',
            'slot_end' => 'end time',
            'duration' => 'slot length',
        ];
    }

    /**
     * Expand the chosen window into back-to-back slots of the chosen duration.
     * The final partial slot (one that would run past the end time) is dropped,
     * so every returned slot is a full-length appointment within the window.
     *
     * @return array<int, array{available_date: string, slot_start: string, slot_end: string}>
     */
    public function generatedSlots(): array
    {
        $date = $this->input('available_date');
        $duration = (int) $this->input('duration');

        $cursor = Carbon::createFromFormat('H:i', $this->input('slot_start'));
        $end = Carbon::createFromFormat('H:i', $this->input('slot_end'));

        $slots = [];
        while ($cursor->copy()->addMinutes($duration)->lessThanOrEqualTo($end)) {
            $slotEnd = $cursor->copy()->addMinutes($duration);
            $slots[] = [
                'available_date' => $date,
                'slot_start' => $cursor->format('H:i'),
                'slot_end' => $slotEnd->format('H:i'),
            ];
            $cursor = $slotEnd;
        }

        return $slots;
    }
}
