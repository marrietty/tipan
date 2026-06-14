<?php

namespace App\Http\Requests;

use App\Models\Specialization;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;

class StoreDoctorRequest extends FormRequest
{
    /**
     * Only an admin reaches this (route role middleware).
     */
    public function authorize(): bool
    {
        return $this->user()?->admin !== null;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:user,email'],
            'specialization_id' => ['required', Rule::exists('specialization', 'id')],
            // Mirror the unique license_number constraint with a clean message
            // before the database rejects the insert.
            'license_number' => ['required', 'string', 'max:100', 'unique:doctor,license_number'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'That email is already in use by another account.',
            'license_number.unique' => 'That license number already belongs to another doctor.',
            'specialization_id.exists' => 'Choose a valid specialization.',
        ];
    }

    public function attributes(): array
    {
        return [
            'specialization_id' => 'specialization',
            'license_number' => 'license number',
        ];
    }
}
