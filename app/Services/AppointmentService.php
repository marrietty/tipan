<?php

namespace App\Services;

use App\Exceptions\SlotUnavailableException;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Schedule;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentService
{
    /**
     * Status id for a freshly booked appointment. The status lookup is seeded
     * with scheduled = 1 (and is part of the read-only schema contract).
     */
    private const STATUS_SCHEDULED = 1;

    /**
     * Book one slot for a patient. The whole thing runs in a single
     * transaction with the slot row locked so two simultaneous bookings cannot
     * both win:
     *
     *   1. Lock the slot row (SELECT ... FOR UPDATE).
     *   2. Re-check is_booked is still false under the lock; if not, refuse.
     *   3. Create the appointment (scheduled, this patient, the slot's doctor).
     *   4. Flip the slot's is_booked to true.
     *
     * The UNIQUE constraint on appointment.schedule_id is the final backstop:
     * if a racing transaction slipped an appointment in, the insert fails and
     * we surface it as a slot-taken condition rather than a raw DB error.
     *
     * @throws SlotUnavailableException
     */
    public function book(Patient $patient, Schedule $slot, ?string $reason = null): Appointment
    {
        try {
            return DB::transaction(function () use ($patient, $slot, $reason) {
                // Lock this slot row for the duration of the transaction.
                $locked = Schedule::whereKey($slot->id)->lockForUpdate()->first();

                if ($locked === null || $locked->is_booked) {
                    throw SlotUnavailableException::make();
                }

                $appointmentDt = Carbon::parse(
                    $locked->available_date->toDateString().' '.$locked->slot_start
                );

                $appointment = Appointment::create([
                    'id' => (string) Str::uuid(),
                    'patient_id' => $patient->id,
                    'doctor_id' => $locked->doctor_id,
                    'schedule_id' => $locked->id,
                    'appointment_dt' => $appointmentDt,
                    'status_id' => self::STATUS_SCHEDULED,
                    'reason' => $reason,
                    'created_at' => Carbon::now(),
                ]);

                $locked->update(['is_booked' => true]);

                return $appointment;
            });
        } catch (QueryException $e) {
            // 23505 = unique_violation: a concurrent booking already claimed
            // this slot. Translate the backstop into a clean refusal.
            if ($this->isUniqueViolation($e)) {
                throw SlotUnavailableException::make();
            }

            throw $e;
        }
    }

    /**
     * Cancel an appointment. In one transaction, delete the appointment row and
     * reset its slot's is_booked to false, so the slot becomes immediately
     * rebookable. Deleting the row (rather than flagging a status) is the
     * schema's intended cancellation model, and releasing the UNIQUE on
     * schedule_id is what frees the slot.
     *
     * Authorization (own appointment, still scheduled) is enforced by the
     * policy before this is reached.
     */
    public function cancel(Appointment $appointment): void
    {
        DB::transaction(function () use ($appointment) {
            // Lock the slot so its is_booked reset cannot race a booking.
            $slot = Schedule::whereKey($appointment->schedule_id)->lockForUpdate()->first();

            $appointment->delete();

            if ($slot !== null) {
                $slot->update(['is_booked' => false]);
            }
        });
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        return ($e->getCode() === '23505')
            || str_contains($e->getMessage(), 'appointment_schedule_id');
    }
}
