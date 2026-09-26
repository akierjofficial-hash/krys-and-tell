<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'visit_id',
        'visit_procedure_id',
        'amount',
        'method',
        'payment_date',
        'notes',
        'submission_token',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class)->withTrashed();
    }

    public function procedure()
    {
        return $this->belongsTo(VisitProcedure::class, 'visit_procedure_id');
    }
}
