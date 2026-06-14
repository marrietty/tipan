<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePrescriptionRequest;
use App\Models\MedicalRecord;
use App\Models\Prescription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;

class PrescriptionController extends Controller
{
    /**
     * Add one medication to a record. Multiple prescriptions on one record
     * represent multiple medications.
     */
    public function store(StorePrescriptionRequest $request, MedicalRecord $record): RedirectResponse
    {
        $this->authorize('create', [Prescription::class, $record]);

        Prescription::create([
            'id' => (string) Str::uuid(),
            'medical_record_id' => $record->id,
            'medication_name' => $request->input('medication_name'),
            'dosage' => $request->input('dosage'),
            'frequency' => $request->input('frequency'),
            'duration_days' => $request->input('duration_days'),
            'instructions' => $request->input('instructions'),
        ]);

        return redirect()
            ->route('doctor.appointments.record.edit', $record->appointment_id)
            ->with('status', 'Medication added.');
    }

    /**
     * Remove a single medication. This is an ordinary delete; the schema's
     * cascade only fires when a whole record is deleted.
     */
    public function destroy(Prescription $prescription): RedirectResponse
    {
        $this->authorize('delete', $prescription);

        $appointmentId = $prescription->medicalRecord->appointment_id;
        $prescription->delete();

        return redirect()
            ->route('doctor.appointments.record.edit', $appointmentId)
            ->with('status', 'Medication removed.');
    }
}
