<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Doctor extends Model
{
    protected $fillable = [
        'name',
        'email',
        'phone',
        'specialty',
        'is_active',
        'sort_order',
        'working_days',
        'work_start_time',
        'work_end_time',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'working_days' => 'array',
    ];

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function unavailabilities()
    {
        return $this->hasMany(DoctorUnavailability::class);
    }
}
