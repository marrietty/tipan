<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Schedule;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    /**
     * Lookup ids from the seeded lookup tables (read-only contract).
     */
    private const ROLE_PATIENT = 1;
    private const ROLE_DOCTOR = 2;
    private const ROLE_ADMIN = 3;

    private const GENDER_MALE = 1;
    private const GENDER_FEMALE = 2;

    private const STATUS_SCHEDULED = 1;
    private const STATUS_COMPLETED = 2;

    /**
     * Load a realistic, Manila-area demo dataset in dependency order.
     * UUID PKs are generated with Str::uuid() so relationships can be wired
     * directly; created_at is set by hand because timestamps are disabled.
     */
    public function run(): void
    {
        $now = Carbon::now();

        // --- Admin -------------------------------------------------------
        $this->createUserWithProfile(
            email: 'liza.mercado@tipan.ph',
            roleId: self::ROLE_ADMIN,
            now: $now,
            makeProfile: fn (string $userId) => Admin::create([
                'id' => (string) Str::uuid(),
                'user_id' => $userId,
                'first_name' => 'Liza',
                'last_name' => 'Mercado',
            ]),
        );

        // --- Doctors -----------------------------------------------------
        $doctorData = [
            [
                'email' => 'ramon.dela.cruz@tipan.ph',
                'first_name' => 'Ramon',
                'last_name' => 'Dela Cruz',
                'specialization_id' => 2, // cardiology
                'license_number' => 'PRC-MD-104238',
                'phone' => '+63 917 412 8830',
            ],
            [
                'email' => 'grace.villanueva@tipan.ph',
                'first_name' => 'Grace',
                'last_name' => 'Villanueva',
                'specialization_id' => 6, // pediatrics
                'license_number' => 'PRC-MD-118764',
                'phone' => '+63 918 305 5521',
            ],
            [
                'email' => 'nestor.aquino@tipan.ph',
                'first_name' => 'Nestor',
                'last_name' => 'Aquino',
                'specialization_id' => 3, // dermatology
                'license_number' => 'PRC-MD-129055',
                'phone' => '+63 920 778 1147',
            ],
        ];

        $doctors = [];
        foreach ($doctorData as $d) {
            $doctors[] = $this->createUserWithProfile(
                email: $d['email'],
                roleId: self::ROLE_DOCTOR,
                now: $now,
                makeProfile: fn (string $userId) => Doctor::create([
                    'id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'first_name' => $d['first_name'],
                    'last_name' => $d['last_name'],
                    'specialization_id' => $d['specialization_id'],
                    'license_number' => $d['license_number'],
                    'phone' => $d['phone'],
                ]),
            );
        }

        // --- Patients ----------------------------------------------------
        $patientData = [
            [
                'email' => 'maria.santos@example.ph',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'date_of_birth' => '1991-03-14',
                'gender_id' => self::GENDER_FEMALE,
                'phone' => '+63 917 220 4471',
                'address' => '42 Kalayaan Ave, Diliman, Quezon City',
            ],
            [
                'email' => 'jose.reyes@example.ph',
                'first_name' => 'Jose',
                'last_name' => 'Reyes',
                'date_of_birth' => '1985-11-02',
                'gender_id' => self::GENDER_MALE,
                'phone' => '+63 918 661 9023',
                'address' => '17 Mabini St, Malate, Manila',
            ],
            [
                'email' => 'angelica.dizon@example.ph',
                'first_name' => 'Angelica',
                'last_name' => 'Dizon',
                'date_of_birth' => '1998-07-25',
                'gender_id' => self::GENDER_FEMALE,
                'phone' => '+63 920 134 7788',
                'address' => '88 Shaw Blvd, Mandaluyong City',
            ],
            [
                'email' => 'paolo.gutierrez@example.ph',
                'first_name' => 'Paolo',
                'last_name' => 'Gutierrez',
                'date_of_birth' => '1979-01-19',
                'gender_id' => self::GENDER_MALE,
                'phone' => '+63 917 909 3312',
                'address' => '5 Aguirre St, BF Homes, Parañaque City',
            ],
        ];

        $patients = [];
        foreach ($patientData as $p) {
            $patients[] = $this->createUserWithProfile(
                email: $p['email'],
                roleId: self::ROLE_PATIENT,
                now: $now,
                makeProfile: fn (string $userId) => Patient::create([
                    'id' => (string) Str::uuid(),
                    'user_id' => $userId,
                    'first_name' => $p['first_name'],
                    'last_name' => $p['last_name'],
                    'date_of_birth' => $p['date_of_birth'],
                    'gender_id' => $p['gender_id'],
                    'phone' => $p['phone'],
                    'address' => $p['address'],
                ]),
            );
        }

        // --- Schedule slots ---------------------------------------------
        // 6 to 8 open slots per doctor across the next two weeks. Start times
        // are distinct per doctor per day (unique_doctor_slot) and every slot
        // ends after it starts (slot_order).
        $slotTimes = [
            ['09:00:00', '09:30:00'],
            ['09:30:00', '10:00:00'],
            ['10:00:00', '10:30:00'],
            ['13:30:00', '14:00:00'],
            ['14:00:00', '14:30:00'],
            ['15:00:00', '15:30:00'],
            ['16:00:00', '16:30:00'],
            ['16:30:00', '17:00:00'],
        ];

        $slotsByDoctor = [];
        foreach ($doctors as $i => $doctor) {
            // Vary count per doctor: 6, 7, 8.
            $count = 6 + $i;
            // Spread slots over weekdays in the next two weeks. Each doctor
            // holds clinic on a different day so dates stay clean and human.
            $clinicWeekday = [Carbon::TUESDAY, Carbon::WEDNESDAY, Carbon::THURSDAY][$i];

            $day1 = $now->copy()->next($clinicWeekday)->startOfDay();
            $day2 = $day1->copy()->addWeek();

            $created = [];
            foreach (range(0, $count - 1) as $n) {
                // First four times on day one, the rest on day two.
                $date = $n < 4 ? $day1 : $day2;
                [$start, $end] = $slotTimes[$n % count($slotTimes)];

                $created[] = Schedule::create([
                    'id' => (string) Str::uuid(),
                    'doctor_id' => $doctor->id,
                    'available_date' => $date->toDateString(),
                    'slot_start' => $start,
                    'slot_end' => $end,
                    'is_booked' => false,
                ]);
            }
            $slotsByDoctor[$doctor->id] = $created;
        }

        // --- Appointments (real booking flow, in a transaction) ----------
        // Each appointment claims one open slot and flips it to booked,
        // mirroring AppointmentService: insert + is_booked update atomically.
        $bookings = [
            // patient index, doctor index, slot index within that doctor, status, reason
            [0, 0, 0, self::STATUS_COMPLETED, 'Follow-up on elevated blood pressure'],
            [1, 1, 1, self::STATUS_SCHEDULED, 'Child wellness check and vaccination review'],
            [2, 2, 2, self::STATUS_SCHEDULED, 'Recurring skin rash on forearms'],
        ];

        $appointments = [];
        foreach ($bookings as [$pi, $di, $si, $statusId, $reason]) {
            $patient = $patients[$pi];
            $doctor = $doctors[$di];
            $slot = $slotsByDoctor[$doctor->id][$si];

            $appointments[] = DB::transaction(function () use ($patient, $doctor, $slot, $statusId, $reason, $now) {
                $appointmentDt = Carbon::parse($slot->available_date->toDateString().' '.$slot->slot_start);

                $appointment = Appointment::create([
                    'id' => (string) Str::uuid(),
                    'patient_id' => $patient->id,
                    'doctor_id' => $doctor->id,
                    'schedule_id' => $slot->id,
                    'appointment_dt' => $appointmentDt,
                    'status_id' => $statusId,
                    'reason' => $reason,
                    'created_at' => $now,
                ]);

                $slot->update(['is_booked' => true]);

                return $appointment;
            });
        }

        // --- Medical record + prescriptions for the completed visit ------
        $completed = $appointments[0];

        $record = MedicalRecord::create([
            'id' => (string) Str::uuid(),
            'patient_id' => $completed->patient_id,
            'appointment_id' => $completed->id,
            'doctor_id' => $completed->doctor_id,
            'diagnosis' => 'Stage 1 hypertension; otherwise stable.',
            'notes' => 'BP 148/92 on arrival. Advised reduced sodium intake and '
                .'daily light exercise. Recheck in four weeks.',
            'recorded_at' => $now,
        ]);

        Prescription::create([
            'id' => (string) Str::uuid(),
            'medical_record_id' => $record->id,
            'medication_name' => 'Amlodipine',
            'dosage' => '5 mg',
            'frequency' => 'Once daily',
            'duration_days' => 30,
            'instructions' => 'Take in the morning with water.',
        ]);

        Prescription::create([
            'id' => (string) Str::uuid(),
            'medical_record_id' => $record->id,
            'medication_name' => 'Losartan',
            'dosage' => '50 mg',
            'frequency' => 'Once daily',
            'duration_days' => 30,
            'instructions' => 'Take in the evening. Monitor for dizziness.',
        ]);
    }

    /**
     * Create the user row first, then the role-specific profile linked by
     * user_id, and return the profile model (its id is what the domain tables
     * reference, not the user id). created_at is set manually because
     * timestamps are disabled.
     *
     * @template TProfile of \Illuminate\Database\Eloquent\Model
     *
     * @param  callable(string):TProfile  $makeProfile
     * @return TProfile
     */
    private function createUserWithProfile(string $email, int $roleId, Carbon $now, callable $makeProfile)
    {
        $user = User::create([
            'id' => (string) Str::uuid(),
            'email' => $email,
            'password_hash' => Hash::make('password'),
            'role_id' => $roleId,
            'created_at' => $now,
        ]);

        return $makeProfile($user->id);
    }
}
