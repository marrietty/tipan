<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Status;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    /**
     * The doctor's own appointments, soonest first, each linking to its record
     * so the doctor can record the visit's outcome.
     */
    public function index(Request $request): View
    {
        $doctor = $request->user()->doctor;

        $appointments = $doctor->appointments()
            ->with(['patient', 'schedule', 'status', 'medicalRecord'])
            ->get()
            ->sortBy(fn (Appointment $a) => $a->appointment_dt)
            ->values();

        return view('doctor.appointments.index', [
            'appointments' => $appointments,
        ]);
    }

    /**
     * Mark a scheduled appointment completed or missed. The row is retained
     * (this is not cancellation) and the slot's is_booked is deliberately left
     * untouched — the visit happened or was missed, so the slot stays consumed.
     */
    public function transition(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('transition', $appointment);

        $validated = $request->validate([
            'status' => ['required', 'in:completed,missed'],
        ]);

        $statusId = Status::where('name', $validated['status'])->value('id');
        $appointment->update(['status_id' => $statusId]);

        $label = $validated['status'] === 'completed' ? 'completed' : 'missed';

        return redirect()
            ->route('doctor.appointments.index')
            ->with('status', "Appointment marked {$label}.");
    }
}
