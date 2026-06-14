<?php

namespace App\Http\Controllers\Patient;

use App\Exceptions\SlotUnavailableException;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAppointmentRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Schedule;
use App\Services\AppointmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function __construct(private readonly AppointmentService $appointments)
    {
    }

    /**
     * Doctor list/search: name, specialization, and a count of upcoming open
     * slots so a patient can tell at a glance who has availability.
     */
    public function doctors(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        $doctors = Doctor::query()
            ->with('specialization')
            ->withCount(['schedules as open_slots_count' => function ($query) {
                $query->where('is_booked', false)
                    ->where('available_date', '>=', Carbon::today());
            }])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('first_name', 'ilike', "%{$search}%")
                        ->orWhere('last_name', 'ilike', "%{$search}%")
                        ->orWhereHas('specialization', fn ($s) => $s->where('display_name', 'ilike', "%{$search}%"));
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('patient.booking.doctors', [
            'doctors' => $doctors,
            'search' => $search,
        ]);
    }

    /**
     * A doctor's real availability: only open, upcoming slots, grouped by date.
     */
    public function availability(Doctor $doctor): View
    {
        $doctor->loadMissing('specialization');

        $slotsByDate = $doctor->schedules()
            ->where('is_booked', false)
            ->where('available_date', '>=', Carbon::today())
            ->orderBy('available_date')
            ->orderBy('slot_start')
            ->get()
            ->groupBy(fn (Schedule $slot) => $slot->available_date->toDateString());

        return view('patient.booking.availability', [
            'doctor' => $doctor,
            'slotsByDate' => $slotsByDate,
        ]);
    }

    /**
     * Book the chosen slot. The service does the locked, transactional work;
     * a slot-taken outcome is surfaced calmly back on the availability screen.
     */
    public function store(StoreAppointmentRequest $request, Doctor $doctor, Schedule $schedule): RedirectResponse
    {
        $patient = $request->user()->patient;

        try {
            $appointment = $this->appointments->book($patient, $schedule, $request->reason());
        } catch (SlotUnavailableException $e) {
            return redirect()
                ->route('patient.booking.availability', $doctor)
                ->with('error', $e->getMessage());
        }

        return redirect()->route('patient.booking.confirmation', $appointment);
    }

    /**
     * Unambiguous confirmation: who, with whom, when, and that it is scheduled.
     */
    public function confirmation(Request $request, Appointment $appointment): View
    {
        // A patient sees only their own confirmation.
        abort_unless($appointment->patient_id === $request->user()->patient?->id, 403);

        $appointment->load(['doctor.specialization', 'schedule', 'status', 'patient']);

        return view('patient.booking.confirmation', [
            'appointment' => $appointment,
        ]);
    }
}
