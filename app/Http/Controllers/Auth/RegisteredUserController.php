<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * Self-registration always creates a patient: a "user" row with the
     * patient role plus a linked "patient" profile, in one transaction.
     * (The full DOB/gender/address form lands with the dedicated
     * registration task; this keeps the flow valid against the schema.)
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:user,email'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $patientRoleId = Role::where('name', 'patient')->value('id');

        $user = DB::transaction(function () use ($validated, $patientRoleId) {
            $user = User::create([
                'id' => (string) Str::uuid(),
                'email' => $validated['email'],
                'password_hash' => Hash::make($validated['password']),
                'role_id' => $patientRoleId,
                'created_at' => Carbon::now(),
            ]);

            Patient::create([
                'id' => (string) Str::uuid(),
                'user_id' => $user->id,
                'first_name' => $validated['first_name'],
                'last_name' => $validated['last_name'],
                // Required by the schema; collected in full on the dedicated
                // registration form. Placeholder until then.
                'date_of_birth' => '1900-01-01',
            ]);

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route($user->homeRoute());
    }
}
