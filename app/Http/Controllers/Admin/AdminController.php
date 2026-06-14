<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreDoctorRequest;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Specialization;
use App\Models\Status;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Overview: total patients, total doctors, and appointments by status.
     * All via aggregate queries — counts and a grouped count — never loading
     * rows. Cancelled appointments are deleted, so they simply won't appear;
     * the counts are honest about the statuses that exist.
     */
    public function dashboard(): View
    {
        // One grouped query for appointment counts by status name.
        $countsByStatus = Appointment::query()
            ->join('status', 'appointment.status_id', '=', 'status.id')
            ->select('status.name', 'status.display_name', DB::raw('count(*) as total'))
            ->groupBy('status.name', 'status.display_name')
            ->pluck('total', 'display_name');

        return view('admin.dashboard', [
            'patientCount' => Patient::count(),
            'doctorCount' => Doctor::count(),
            'appointmentTotal' => (int) $countsByStatus->sum(),
            'countsByStatus' => $countsByStatus,
        ]);
    }

    /**
     * All doctors with specialization, license, and a count of upcoming open
     * slots. The open-slot count is an aggregate (withCount), not a row load.
     */
    public function doctors(): View
    {
        $doctors = Doctor::query()
            ->with('specialization')
            ->withCount(['schedules as open_slots_count' => function ($query) {
                $query->where('is_booked', false)
                    ->where('available_date', '>=', Carbon::today());
            }])
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('admin.doctors.index', [
            'doctors' => $doctors,
        ]);
    }

    public function createDoctor(): View
    {
        return view('admin.doctors.create', [
            'specializations' => Specialization::orderBy('display_name')->get(),
        ]);
    }

    /**
     * Create a doctor: a user row (doctor role, hashed password) plus a doctor
     * profile, in one transaction — mirroring how registration creates a
     * patient. Validation catches duplicate email/license before the DB does.
     */
    public function storeDoctor(StoreDoctorRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $doctorRoleId = Role::where('name', 'doctor')->value('id');

        DB::transaction(function () use ($validated, $doctorRoleId) {
            $user = User::create([
                'id' => (string) Str::uuid(),
                'email' => $validated['email'],
                'password_hash' => Hash::make($validated['password']),
                'role_id' => $doctorRoleId,
                'created_at' => Carbon::now(),
            ]);

            Doctor::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                'specialization_id' => $validated['specialization_id'],
                'license_number' => $validated['license_number'],
                'phone' => $validated['phone'] ?? null,
            ]);
        });

        return redirect()
            ->route('admin.doctors.index')
            ->with('status', 'Dr. '.$validated['first_name'].' '.$validated['last_name'].' was added.');
    }

    /**
     * All patients with contact details and an appointment count. View-only;
     * admins do not edit clinical data.
     */
    public function patients(): View
    {
        $patients = Patient::query()
            ->withCount('appointments')
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('admin.patients.index', [
            'patients' => $patients,
        ]);
    }

    /**
     * System-wide appointments, most-recent-first, read-only.
     */
    public function appointments(): View
    {
        $appointments = Appointment::query()
            ->with(['patient', 'doctor.specialization', 'status', 'schedule'])
            ->orderByDesc('appointment_dt')
            ->get();

        return view('admin.appointments.index', [
            'appointments' => $appointments,
        ]);
    }
}
