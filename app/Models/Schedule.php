<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Schedule extends Model
{
    protected $table = 'schedule';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'doctor_id',
        'available_date',
        'slot_start',
        'slot_end',
        'is_booked',
    ];

    protected function casts(): array
    {
        return [
            'available_date' => 'date',
            'is_booked' => 'boolean',
        ];
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function appointment(): HasOne
    {
        return $this->hasOne(Appointment::class, 'schedule_id');
    }
}
