<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'visit_id',
        'patient_id',
        'service_id',
        'total_cost',
        'downpayment',
        'balance',
        'months',
        'start_date',
        'status',
        'is_open_contract', // ✅ NEW
    ];

    protected $casts = [
        'is_open_contract' => 'boolean',
    ];

    protected $attributes = [
        'status' => 'Pending',
        'is_open_contract' => false,
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function payments()
    {
        return $this->hasMany(InstallmentPayment::class, 'installment_plan_id');
    }
}
