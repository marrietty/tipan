<?php

namespace App\Policies;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\User;

class MedicalRecordPolicy
{
    /**
     * A medical record may be viewed by the patient it belongs to (read-only)
     * or by the doctor who attended it.
     */
    public function view(User $user, MedicalRecord $record): bool
    {
        if ($patient = $user->patient) {
            return $record->patient_id === $patient->id;
        }

        if ($doctor = $user->doctor) {
            return $record->doctor_id === $doctor->id;
        }

        return false;
    }

    /**
     * A doctor may create or edit the record for an appointment only when that
     * appointment is their own. The record's doctor_id and patient_id are
     * derived from the appointment, so it can never name a different doctor or
     * patient than the visit it documents.
     */
    public function manageForAppointment(User $user, Appointment $appointment): bool
    {
        $doctor = $user->doctor;

        return $doctor !== null && $appointment->doctor_id === $doctor->id;
    }

    /**
     * A doctor may edit a record they attended.
     */
    public function update(User $user, MedicalRecord $record): bool
    {
        $doctor = $user->doctor;

        return $doctor !== null && $record->doctor_id === $doctor->id;
    }
}
