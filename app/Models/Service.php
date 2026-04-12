<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'base_price',
        'allow_custom_price',
        'description',
        'color',
        'duration_minutes',
        'restrict_to_assigned_doctors',
    ];

    protected $casts = [
        'allow_custom_price' => 'boolean',
        'base_price' => 'decimal:2',
        'restrict_to_assigned_doctors' => 'boolean',
    ];

    public function visitProcedures()
    {
        return $this->hasMany(VisitProcedure::class);
    }

    public function assignedDoctors()
    {
        return $this->belongsToMany(Doctor::class, 'doctor_service')
            ->withTimestamps();
    }
}
