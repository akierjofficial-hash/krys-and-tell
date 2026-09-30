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
        'is_walk_in',
        'walk_in_note',
        'restrict_to_assigned_doctors',
        'is_staff_only',
        'internal_code',
    ];

    protected $casts = [
        'allow_custom_price' => 'boolean',
        'base_price' => 'decimal:2',
        'restrict_to_assigned_doctors' => 'boolean',
        'is_walk_in' => 'boolean',
        'is_staff_only' => 'boolean',
    ];

    public function scopePubliclyAvailable($query)
    {
        return $query->where('is_staff_only', false);
    }

    public function isRecement(): bool
    {
        return $this->internal_code === 'recement';
    }

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
