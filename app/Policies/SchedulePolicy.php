<?php

namespace App\Policies;

use App\Models\Schedule;
use App\Models\User;

class SchedulePolicy
{
    /**
     * A doctor may act on a slot only if it belongs to their own doctor
     * profile. Patients and admins have no business in this area; the route's
     * role middleware already keeps them out, and these checks add row-level
     * ownership on top.
     */
    private function ownsSlot(User $user, Schedule $schedule): bool
    {
        $doctor = $user->doctor;

        return $doctor !== null && $schedule->doctor_id === $doctor->id;
    }

    public function view(User $user, Schedule $schedule): bool
    {
        return $this->ownsSlot($user, $schedule);
    }

    /**
     * A slot may be deleted only by its owning doctor, and only while it is
     * unbooked. Deleting a booked slot would orphan its appointment, which the
     * database RESTRICT would reject; this stops it before that.
     */
    public function delete(User $user, Schedule $schedule): bool
    {
        return $this->ownsSlot($user, $schedule) && ! $schedule->is_booked;
    }
}
