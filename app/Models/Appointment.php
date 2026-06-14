<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Appointment extends Model
{
    protected $table = 'appointment';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The appointment table carries only created_at (no updated_at), so the
     * automatic timestamp pair is disabled and created_at is cast/managed alone.
     */
    public $timestamps = false;

    protected $fillable = [
        'id',
        'patient_id',
        'doctor_id',
        'schedule_id',
        'appointment_dt',
        'status_id',
        'reason',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'appointment_dt' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }

    public function status(): BelongsTo
    {
        return $this->belongsTo(Status::class, 'status_id');
    }

    public function medicalRecord(): HasOne
    {
        return $this->hasOne(MedicalRecord::class, 'appointment_id');
    }
}
