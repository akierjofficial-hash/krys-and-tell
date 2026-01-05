<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientInformedConsent extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'initials',

        'patient_signature_path',
        'patient_signed_at',

        'dentist_signature_path',
        'dentist_signed_at',
    ];

    protected $casts = [
        'initials' => 'array',
        'patient_signed_at' => 'datetime',
        'dentist_signed_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
