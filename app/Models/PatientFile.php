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
        'file_path',
        'mime',
        'size',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class)->withTrashed();
    }
}
