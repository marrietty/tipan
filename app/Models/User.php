<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'user';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * The "user" table carries only created_at (no updated_at), so Eloquent's
     * automatic timestamp pair is disabled and created_at is cast/managed alone.
     */
    public $timestamps = false;

    protected $fillable = [
        'id',
        'email',
        'password_hash',
        'role_id',
        'created_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'created_at' => 'datetime',
        ];
    }

    /**
     * Breeze/auth uses the password_hash column for the credential.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    /**
     * The "user" table has no remember_token column, so disable "remember me"
     * persistence: report no remember-token attribute and make the setter a
     * no-op. Without this, a remembered login tries to update a missing column.
     */
    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void
    {
        // Intentionally not stored; the schema has no remember_token column.
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function patient(): HasOne
    {
        return $this->hasOne(Patient::class, 'user_id');
    }

    public function doctor(): HasOne
    {
        return $this->hasOne(Doctor::class, 'user_id');
    }

    public function admin(): HasOne
    {
        return $this->hasOne(Admin::class, 'user_id');
    }

    /**
     * The machine name of this user's role (patient, doctor, admin).
     */
    public function roleName(): string
    {
        return $this->role->name;
    }

    public function hasRole(string $role): bool
    {
        return $this->roleName() === $role;
    }

    /**
     * The role-specific profile row (patient, doctor, or admin) for this user.
     */
    public function profile(): ?Model
    {
        return match ($this->roleName()) {
            'patient' => $this->patient,
            'doctor' => $this->doctor,
            'admin' => $this->admin,
            default => null,
        };
    }

    /**
     * The user's real name, drawn from their profile rather than the email.
     */
    public function displayName(): string
    {
        $profile = $this->profile();

        return $profile
            ? trim($profile->first_name.' '.$profile->last_name)
            : $this->email;
    }

    /**
     * The dashboard route a user should land on, by role. Single source of
     * truth for post-login redirection and navigation.
     */
    public function homeRoute(): string
    {
        return match ($this->roleName()) {
            'patient' => 'patient.dashboard',
            'doctor' => 'doctor.dashboard',
            'admin' => 'admin.dashboard',
            default => 'login',
        };
    }
}
