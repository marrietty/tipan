<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMedicalRecordRequest;
use App\Models\Appointment;
use App\Models\MedicalRecord;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class MedicalRecordController extends Controller
{
    /**
     * Show the record form for an appointment. If a record already exists, the
     * doctor edits it (one record per appointment); otherwise this is a fresh
     * record. Either way the patient and visit context is shown for legibility.
     */
    public function edit(Appointment $appointment): View
    {
        $this->authorize('manageForAppointment', [MedicalRecord::class, $appointment]);

        $appointment->load(['patient', 'doctor.specialization', 'schedule', 'status']);

        $record = $appointment->medicalRecord()->with('prescriptions')->first();

        return view('doctor.records.edit', [
            'appointment' => $appointment,
            'record' => $record,
        ]);
    }

    /**
     * Create or update the record for an appointment. doctor_id and patient_id
     * come from the appointment, never from input, so the record can only
     * document its own visit. A UNIQUE violation on appointment_id (a record
     * already exists) is translated into an update rather than a raw error.
     */
    public function store(StoreMedicalRecordRequest $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('manageForAppointment', [MedicalRecord::class, $appointment]);

        $existing = $appointment->medicalRecord()->first();

        if ($existing) {
            $existing->update($request->only('diagnosis', 'notes'));

            return $this->backToRecord($appointment, 'Record updated.');
        }

        try {
            MedicalRecord::create([
                'id' => (string) Str::uuid(),
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'diagnosis' => $request->input('diagnosis'),
                'notes' => $request->input('notes'),
                'recorded_at' => Carbon::now(),
            ]);
        } catch (QueryException $e) {
            // A record was created concurrently; fall back to updating it so a
            // duplicate never surfaces as a raw unique-violation error.
            if ($this->isUniqueViolation($e)) {
                $appointment->medicalRecord()->first()?->update($request->only('diagnosis', 'notes'));

                return $this->backToRecord($appointment, 'Record updated.');
            }

            throw $e;
        }

        return $this->backToRecord($appointment, 'Record saved.');
    }

    private function backToRecord(Appointment $appointment, string $status): RedirectResponse
    {
        return redirect()
            ->route('doctor.appointments.record.edit', $appointment)
            ->with('status', $status);
    }

    private function isUniqueViolation(QueryException $e): bool
    {
        return ($e->getCode() === '23505')
            || str_contains($e->getMessage(), 'medical_record_appointment_id');
    }
}
