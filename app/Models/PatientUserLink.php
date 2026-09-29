<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PatientUserLink extends Model
{
    protected $fillable = [
        'patient_id',
        'user_id',
        'relationship',
        'verification_note',
        'verified_at',
        'verified_by_user_id',
        'unlinked_at',
        'unlinked_by_user_id',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'unlinked_at' => 'datetime',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_user_id')->withTrashed();
    }

    public function unlinkedBy()
    {
        return $this->belongsTo(User::class, 'unlinked_by_user_id')->withTrashed();
    }

    public function scopeActive($query)
    {
        return $query->whereNull('unlinked_at');
    }
}
