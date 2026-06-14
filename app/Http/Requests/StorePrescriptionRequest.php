<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePrescriptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->doctor !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'medication_name' => ['required', 'string', 'max:255'],
            'dosage' => ['required', 'string', 'max:100'],
            'frequency' => ['required', 'string', 'max:100'],
            // Optional, but if given must be positive — mirrors the CHECK
            // (duration_days > 0) so the doctor gets a clean message first.
            'duration_days' => ['nullable', 'integer', 'min:1'],
            'instructions' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'duration_days.min' => 'Duration must be at least one day.',
            'duration_days.integer' => 'Duration must be a whole number of days.',
        ];
    }

    public function attributes(): array
    {
        return [
            'medication_name' => 'medication',
            'duration_days' => 'duration',
        ];
    }
}
