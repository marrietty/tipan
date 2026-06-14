<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Doctor\AppointmentController as DoctorAppointmentController;
use App\Http\Controllers\Doctor\MedicalRecordController;
use App\Http\Controllers\Doctor\PrescriptionController;
use App\Http\Controllers\Doctor\ScheduleController;
use App\Http\Controllers\Patient\AppointmentController;
use App\Http\Controllers\Patient\BookingController;
use App\Http\Controllers\Patient\MedicalHistoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // Authenticated visitors go straight to their role's home; everyone else
    // sees the public landing page.
    if ($user = Auth::user()) {
        return redirect()->route($user->homeRoute());
    }

    return view('welcome');
});

/*
 * Role areas. Each group is guarded by auth plus the role middleware, which
 * compares the user's role (via the Role relation) against the allowed roles
 * and aborts 403 on a mismatch. Dashboards are placeholders for now.
 */

Route::middleware(['auth', 'role:patient'])->prefix('patient')->name('patient.')->group(function () {
    Route::view('/', 'patient.dashboard')->name('dashboard');

    Route::prefix('booking')->name('booking.')->group(function () {
        Route::get('doctors', [BookingController::class, 'doctors'])->name('doctors');
        Route::get('doctors/{doctor}', [BookingController::class, 'availability'])->name('availability');
        Route::post('doctors/{doctor}/slots/{schedule}', [BookingController::class, 'store'])->name('store');
        Route::get('confirmation/{appointment}', [BookingController::class, 'confirmation'])->name('confirmation');
    });

    Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');
    Route::delete('appointments/{appointment}', [AppointmentController::class, 'destroy'])->name('appointments.destroy');

    Route::get('records', [MedicalHistoryController::class, 'index'])->name('records.index');
});

Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::view('/', 'doctor.dashboard')->name('dashboard');

    Route::get('schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::get('schedule/create', [ScheduleController::class, 'create'])->name('schedule.create');
    Route::post('schedule', [ScheduleController::class, 'store'])->name('schedule.store');
    Route::delete('schedule/{schedule}', [ScheduleController::class, 'destroy'])->name('schedule.destroy');

    Route::get('appointments', [DoctorAppointmentController::class, 'index'])->name('appointments.index');
    Route::patch('appointments/{appointment}/status', [DoctorAppointmentController::class, 'transition'])->name('appointments.transition');
    Route::get('appointments/{appointment}/record', [MedicalRecordController::class, 'edit'])->name('appointments.record.edit');
    Route::post('appointments/{appointment}/record', [MedicalRecordController::class, 'store'])->name('appointments.record.store');

    Route::post('records/{record}/prescriptions', [PrescriptionController::class, 'store'])->name('records.prescriptions.store');
    Route::delete('prescriptions/{prescription}', [PrescriptionController::class, 'destroy'])->name('prescriptions.destroy');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');

    Route::get('doctors', [AdminController::class, 'doctors'])->name('doctors.index');
    Route::get('doctors/create', [AdminController::class, 'createDoctor'])->name('doctors.create');
    Route::post('doctors', [AdminController::class, 'storeDoctor'])->name('doctors.store');

    Route::get('patients', [AdminController::class, 'patients'])->name('patients.index');
    Route::get('appointments', [AdminController::class, 'appointments'])->name('appointments.index');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::fallback(function () {
    // For an authenticated user, send them to their role's home;
    // otherwise show the public landing page (or a custom 404).
    if ($user = Auth::user()) {
        return redirect()->route($user->homeRoute());
    }

    return redirect('/'); // or: abort(404), or view('errors.404')
});


require __DIR__.'/auth.php';
