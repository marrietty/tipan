<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

class AppointmentPolicy
{
    /**
     * Status id for a scheduled appointment (the only state a patient may
     * cancel). The status lookup is seeded with scheduled = 1.
     */
    private const STATUS_SCHEDULED = 1;

    /**
     * A patient may view only their own appointment.
     */
    public function view(User $user, Appointment $appointment): bool
    {
        return $this->ownedByPatient($user, $appointment);
    }

    /**
     * A patient may cancel only their own appointment, and only while it is
     * still scheduled. Completed or missed appointments are a historical record
     * and cannot be cancelled.
     */
    public function cancel(User $user, Appointment $appointment): bool
    {
        return $this->ownedByPatient($user, $appointment)
            && $appointment->status_id === self::STATUS_SCHEDULED;
    }

    /**
     * A doctor may mark their own appointment completed or missed, and only
     * while it is still scheduled. Completed and missed are terminal states.
     */
    public function transition(User $user, Appointment $appointment): bool
    {
        $doctor = $user->doctor;

        return $doctor !== null
            && $appointment->doctor_id === $doctor->id
            && $appointment->status_id === self::STATUS_SCHEDULED;
    }

    private function ownedByPatient(User $user, Appointment $appointment): bool
    {
        $patient = $user->patient;

        return $patient !== null && $appointment->patient_id === $patient->id;
    }
}
