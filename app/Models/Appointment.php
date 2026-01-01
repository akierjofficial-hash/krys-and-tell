<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
    'patient_id',
    'service_id',
    'appointment_date',
    'appointment_time',
    'duration_minutes',
    'status',
    'notes',
    'dentist_name',
];



    // Relationship to Patient
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    // Relationship to Service
    public function service()
    {
        return $this->belongsTo(Service::class);
    }
    public function doctor()
{
    return $this->belongsTo(\App\Models\Doctor::class, 'doctor_id');
}



}
