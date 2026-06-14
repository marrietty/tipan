<?php

namespace App\Policies;

use App\Models\MedicalRecord;
use App\Models\Prescription;
use App\Models\User;

class PrescriptionPolicy
{
    /**
     * A doctor may add a prescription to a record they attended.
     */
    public function create(User $user, MedicalRecord $record): bool
    {
        $doctor = $user->doctor;

        return $doctor !== null && $record->doctor_id === $doctor->id;
    }

    /**
     * A doctor may remove a prescription from a record they attended.
     */
    public function delete(User $user, Prescription $prescription): bool
    {
        $doctor = $user->doctor;

        return $doctor !== null
            && $prescription->medicalRecord->doctor_id === $doctor->id;
    }
}
