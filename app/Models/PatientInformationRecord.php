<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientInformationRecord extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',

        'nickname',
        'occupation',
        'dental_insurance',
        'effective_date',

        'home_no',
        'office_no',
        'fax_no',

        'is_minor',
        'guardian_name',
        'guardian_occupation',

        'referral_source',
        'consultation_reason',

        'previous_dentist',
        'last_dental_visit',

        'physician_name',
        'physician_specialty',

        'good_health',
        'under_treatment',
        'treatment_condition',

        'serious_illness',
        'serious_illness_details',

        'hospitalized',
        'hospitalized_reason',

        'taking_medication',
        'medications',
        'takes_aspirin',

        'allergies',
        'allergies_other',

        'tobacco_use',
        'alcohol_use',
        'dangerous_drugs',

        'bleeding_time',

        'pregnant',
        'nursing',
        'birth_control_pills',

        'blood_type',
        'blood_pressure',

        'medical_conditions',
        'medical_conditions_other',

        'signature_path',
        'signed_at',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'last_dental_visit' => 'date',
        'signed_at' => 'datetime',

        'is_minor' => 'boolean',

        'good_health' => 'boolean',
        'under_treatment' => 'boolean',
        'serious_illness' => 'boolean',
        'hospitalized' => 'boolean',

        'taking_medication' => 'boolean',
        'takes_aspirin' => 'boolean',

        'tobacco_use' => 'boolean',
        'alcohol_use' => 'boolean',
        'dangerous_drugs' => 'boolean',

        'pregnant' => 'boolean',
        'nursing' => 'boolean',
        'birth_control_pills' => 'boolean',

        'allergies' => 'array',
        'medical_conditions' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }
}
