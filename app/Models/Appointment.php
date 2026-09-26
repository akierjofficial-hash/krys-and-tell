<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_PENDING = ['pending'];
    public const STATUS_ACTIVE = ['upcoming', 'approved', 'confirmed', 'scheduled', 'walked_in'];
    public const STATUS_COMPLETED = ['completed', 'done'];
    public const STATUS_CANCELLED = ['canceled', 'cancelled', 'declined', 'rejected'];

    public const STATUS_DASHBOARD = [
        'pending', 'upcoming', 'approved', 'confirmed', 'scheduled', 'walked_in',
    ];

    protected $fillable = [
        'patient_id',
        'service_id',
        'doctor_id',
        'appointment_date',
        'appointment_time',
        'duration_minutes',
        'status',
        'is_walk_in_request',
        'notes',
        'dentist_name',

        // ✅ staff note / reason
        'staff_note',
    ];

    protected $casts = [
        'is_walk_in_request' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class)->withTrashed();
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function doctor()
    {
        return $this->belongsTo(\App\Models\Doctor::class, 'doctor_id');
    }

    public function scopeDashboardActive($query)
    {
        return $query->whereIn('status', self::STATUS_DASHBOARD);
    }

    public function scopeUpcomingActive($query)
    {
        return $query->whereIn('status', self::STATUS_ACTIVE);
    }

    public function displayPatientName(): string
    {
        $linked = trim(($this->patient?->first_name ?? '') . ' ' . ($this->patient?->last_name ?? ''));
        $public = trim(($this->public_first_name ?? '') . ' ' . ($this->public_last_name ?? ''));

        return $linked ?: ($public ?: ($this->public_name ?: 'Unlinked booking'));
    }

    public function displayDentistName(): string
    {
        return $this->doctor?->name ?: ($this->dentist_name ?: 'Unassigned');
    }
}
