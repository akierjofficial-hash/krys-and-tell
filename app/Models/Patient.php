<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Patient extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'first_name',
        'last_name',
        'middle_name',
        'birthdate',
        'gender',
        'contact_number',
        'email',
        'address',
        'notes',
    ];

public function files()
{
    return $this->hasMany(\App\Models\PatientFile::class);
}
public function visits()
{
    return $this->hasMany(Visit::class);
}
public function informationRecord()
{
    return $this->hasOne(\App\Models\PatientInformationRecord::class);
}

public function informedConsent()
{
    return $this->hasOne(\App\Models\PatientInformedConsent::class);
}


public function payments()
{
    return $this->hasManyThrough(
        Payment::class,
        Visit::class,
        'patient_id', 
        'visit_id',   
        'id',         
        'id'         
    );
}

public function appointments()
{
    return $this->hasMany(Appointment::class);
}


}
