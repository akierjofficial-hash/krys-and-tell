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
    // Payments are linked to patients through visits:
    // patients -> visits (patient_id) -> payments (visit_id)
    return $this->hasManyThrough(
        Payment::class,
        Visit::class,
        'patient_id', // Foreign key on visits table
        'visit_id',   // Foreign key on payments table
        'id',         // Local key on patients table
        'id'          // Local key on visits table
    );
}

public function appointments()
{
    return $this->hasMany(Appointment::class);
}


}
