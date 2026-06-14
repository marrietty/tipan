<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments)
    {
    }

    /**
     * The patient's own appointments, split into upcoming and past so the next
     * visit is easy to find. Upcoming is sorted soonest-first; past is most
     * recent first.
     */
    public function index(Request $request): View
    {
        $patient = $request->user()->patient;

        $appointments = $patient->appointments()
            ->with(['doctor.specialization', 'schedule', 'status'])
            ->get();

        $now = Carbon::now();

        $upcoming = $appointments
            ->filter(fn (Appointment $a) => $a->appointment_dt->greaterThanOrEqualTo($now))
            ->sortBy('appointment_dt')
            ->values();

        $past = $appointments
            ->filter(fn (Appointment $a) => $a->appointment_dt->lessThan($now))
            ->sortByDesc('appointment_dt')
            ->values();

        return view('patient.appointments.index', [
            'upcoming' => $upcoming,
            'past' => $past,
        ]);
    }

    /**
     * Cancel an appointment. The policy guards ownership and that it is still
     * scheduled; the service deletes the row and frees the slot in one
     * transaction.
     */
    public function destroy(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorize('cancel', $appointment);

        $this->appointments->cancel($appointment);

        return redirect()
            ->route('patient.appointments.index')
            ->with('status', 'Your appointment was cancelled and the time is open again.');
    }
}
