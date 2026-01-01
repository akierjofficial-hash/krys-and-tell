<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

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
