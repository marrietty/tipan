<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreMedicalRecordRequest extends FormRequest
{
    /**
     * The route's policy check confirms the doctor owns the appointment; only
     * doctors reach this. Authorization holds here.
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
        return [
            'diagnosis' => ['required', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'diagnosis.required' => 'A diagnosis is needed to save the record.',
        ];
    }
}
