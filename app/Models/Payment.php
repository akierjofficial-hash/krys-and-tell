<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'amount',
        'method',
        'payment_date',
        'installment_plan_id', // nullable
        'notes'
    ];

    // Payment belongs to a Visit
public function visit()
{
    return $this->belongsTo(Visit::class);
}

// Payment has patient through Visit
public function patient()
{
    return $this->hasOneThrough(Patient::class, Visit::class, 'id', 'id', 'visit_id', 'patient_id');
}

    
}
