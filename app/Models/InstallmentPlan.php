<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InstallmentPlan extends Model
{
    use HasFactory;

    public const STATUS_PENDING        = 'Pending';
    public const STATUS_PARTIALLY_PAID = 'Partially Paid';
    public const STATUS_FULLY_PAID     = 'Fully Paid';
    public const STATUS_COMPLETED      = 'Completed';

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
        'is_open_contract',
    ];

    protected $casts = [
        'is_open_contract' => 'boolean',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'is_open_contract' => false,
    ];

    /**
     * Normalize status to prevent DB CHECK constraint violations.
     * Example: "COMPLETED" -> "Completed"
     */
    public function setStatusAttribute($value): void
    {
        if ($value === null) {
            $this->attributes['status'] = null;
            return;
        }

        $v = strtolower(trim((string)$value));

        $map = [
            'pending'         => self::STATUS_PENDING,

            'partially paid'  => self::STATUS_PARTIALLY_PAID,
            'partial'         => self::STATUS_PARTIALLY_PAID,
            'partially_paid'  => self::STATUS_PARTIALLY_PAID,

            'fully paid'      => self::STATUS_FULLY_PAID,
            'full'            => self::STATUS_FULLY_PAID,
            'fully_paid'      => self::STATUS_FULLY_PAID,

            'completed'       => self::STATUS_COMPLETED,
            'complete'        => self::STATUS_COMPLETED,
        ];

        $this->attributes['status'] = $map[$v] ?? $value;
    }

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
