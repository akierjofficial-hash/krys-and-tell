<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

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
}
