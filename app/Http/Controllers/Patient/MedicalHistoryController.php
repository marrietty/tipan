<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MedicalHistoryController extends Controller
{
    /**
     * The patient's own medical history, read-only: each record with its
     * diagnosis, notes, attending doctor, date, and medications, most recent
     * first. Aggregated in query; no separate history table is needed.
     */
    public function index(Request $request): View
    {
        $patient = $request->user()->patient;

        $records = $patient->medicalRecords()
            ->with(['doctor.specialization', 'prescriptions'])
            ->orderByDesc('recorded_at')
            ->get();

        return view('patient.records.index', [
            'records' => $records,
        ]);
    }
}
