<?php

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
});

Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::view('/', 'doctor.dashboard')->name('dashboard');
});

Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::view('/', 'admin.dashboard')->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
