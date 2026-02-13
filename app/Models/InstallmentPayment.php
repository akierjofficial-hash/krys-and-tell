<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallmentPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'installment_plan_id',
        'visit_id',
        'month_number',
        'amount',
        'method',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function plan()
    {
        return $this->belongsTo(InstallmentPlan::class, 'installment_plan_id')->withTrashed();
    }

    public function visit()
    {
        return $this->belongsTo(Visit::class)->withTrashed();
    }
}
