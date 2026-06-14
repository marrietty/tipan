<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Gender extends Model
{
    protected $table = 'gender';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'display_name',
    ];

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class, 'gender_id');
    }
}
