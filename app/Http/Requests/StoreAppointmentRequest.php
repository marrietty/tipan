<?php

namespace App\Http\Requests;

use App\Models\Schedule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreAppointmentRequest extends FormRequest
{
    /**
     * Only a patient with a profile reaches this; booking always uses their
     * own profile id, so authorization holds here.
     */
    public function authorize(): bool
    {
        return $this->user()?->patient !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // The schema caps reason at TEXT; keep a sane UI limit.
            'reason' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Confirm the chosen slot belongs to the doctor in the route and is still
     * open, before the service tries to book it. This gives a clean message;
     * the service's locked re-check and the UNIQUE constraint remain the
     * authoritative guards against a race.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            /** @var Schedule $slot */
            $slot = $this->route('schedule');
            $doctor = $this->route('doctor');

            if ($slot->doctor_id !== $doctor->id) {
                $validator->errors()->add('schedule', 'That slot is not offered by this doctor.');

                return;
            }

            if ($slot->is_booked) {
                $validator->errors()->add('schedule', 'That slot has already been booked.');
            }
        });
    }

    public function reason(): ?string
    {
        $reason = trim((string) $this->input('reason'));

        return $reason === '' ? null : $reason;
    }
}
