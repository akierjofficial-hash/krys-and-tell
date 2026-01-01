<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'dentist_name',
        'visit_date',
        'status',
        'notes',
        'price',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class, 'doctor_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function procedures()
    {
        return $this->hasMany(VisitProcedure::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
