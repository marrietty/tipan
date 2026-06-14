<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Status extends Model
{
    protected $table = 'status';

    public $timestamps = false;

    protected $fillable = [
        'name',
        'display_name',
    ];

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'status_id');
    }
}
