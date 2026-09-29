<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'password_set',
        'role',
        'is_active',
        'last_login_at',
        'google_id',
        'notify_24h',
        'notify_1h',
        'email_verified_at',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'password_set'      => 'boolean',
            'is_active'         => 'boolean',
            'last_login_at'     => 'datetime',
            'notify_24h'        => 'boolean',
            'notify_1h'         => 'boolean',
        ];
    }

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function patientLinks()
    {
        return $this->hasMany(PatientUserLink::class);
    }

    public function verifiedPatients()
    {
        return $this->belongsToMany(Patient::class, 'patient_user_links')
            ->wherePivotNull('unlinked_at')
            ->withPivot(['id', 'relationship', 'verification_note', 'verified_at', 'verified_by_user_id'])
            ->withTimestamps();
    }
}
