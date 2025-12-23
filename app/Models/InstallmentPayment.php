<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPayment extends Model
{
    protected $fillable = [
        'installment_plan_id',
        'month_number',
        'amount',
        'method',
        'payment_date',
    ];

    public function plan()
    {
        return $this->belongsTo(InstallmentPlan::class);
    }
}



