<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PatientFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'patient_id',
        'title',
        'original_name',
        'file_path',
        'storage_disk',
        'mime',
        'size',
        'patient_visible',
    ];

    protected $casts = [
        'patient_visible' => 'boolean',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }
}
