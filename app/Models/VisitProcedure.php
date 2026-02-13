<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitProcedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'service_id',
        'tooth_number',
        'surface',
        'shade',
        'price',
        'notes',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class)->withTrashed();
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }
}
