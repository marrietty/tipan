<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Prescription extends Model
{
    protected $table = 'prescription';

    protected $keyType = 'string';

    public $incrementing = false;

    public $timestamps = false;

    protected $fillable = [
        'id',
        'medical_record_id',
        'medication_name',
        'dosage',
        'frequency',
        'duration_days',
        'instructions',
    ];

    protected function casts(): array
    {
        return [
            'duration_days' => 'integer',
        ];
    }

    public function medicalRecord(): BelongsTo
    {
        return $this->belongsTo(MedicalRecord::class, 'medical_record_id');
    }
}
