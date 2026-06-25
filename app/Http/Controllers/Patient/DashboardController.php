<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the patient dashboard.
     */
    public function __invoke(Request $request): View
    {
        $patient = $request->user()->patient;

        // Fetch upcoming appointment (closest future one)
        $now = Carbon::now();
        $nextAppointment = $patient->appointments()
            ->with(['doctor.user', 'doctor.specialization', 'schedule'])
            ->where('appointment_dt', '>=', $now)
            ->orderBy('appointment_dt', 'asc')
            ->first();

        // Count for empty states
        $appointmentsCount = $patient->appointments()->count();
        $recordsCount = $patient->medicalRecords()->count();

        // Time-aware greeting
        $hour = Carbon::now()->format('H');
        if ($hour < 12) {
            $greeting = 'Good morning';
        } elseif ($hour < 17) {
            $greeting = 'Good afternoon';
        } else {
            $greeting = 'Good evening';
        }

        return view('patient.dashboard', [
            'nextAppointment' => $nextAppointment,
            'appointmentsCount' => $appointmentsCount,
            'recordsCount' => $recordsCount,
            'greeting' => $greeting,
        ]);
    }
}
