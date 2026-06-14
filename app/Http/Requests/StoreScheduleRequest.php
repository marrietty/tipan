<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreScheduleRequest extends FormRequest
{
    /**
     * Only a doctor reaches this request (route role middleware), and a slot is
     * always created for their own profile, so authorization holds here.
     */
    public function authorize(): bool
    {
        return $this->user()?->doctor !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $doctorId = $this->user()->doctor->id;

        return [
            'available_date' => ['required', 'date', 'after_or_equal:today'],
            'slot_end' => ['required', 'date_format:H:i', 'after:slot_start'],

            // Mirror the unique_doctor_slot constraint (doctor, date, start) so
            // a friendly message fires before the database rejects the insert.
            'slot_start' => [
                'required',
                'date_format:H:i',
                Rule::unique('schedule', 'slot_start')
                    ->where(fn ($query) => $query
                        ->where('doctor_id', $doctorId)
                        ->where('available_date', $this->input('available_date'))),
            ],
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
            'slot_start.unique' => 'You already have a slot starting at that time on that date.',
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
        ];
    }

    /**
     * Validated slot data ready for creation, scoped to this doctor.
     *
     * @return array<string, mixed>
     */
    public function slotAttributes(): array
    {
        return [
            'doctor_id' => $this->user()->doctor->id,
            'available_date' => $this->input('available_date'),
            'slot_start' => $this->input('slot_start'),
            'slot_end' => $this->input('slot_end'),
            'is_booked' => false,
        ];
    }
}
