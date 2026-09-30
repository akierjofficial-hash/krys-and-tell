<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class InstallmentPlan extends Model
{
    use HasFactory, SoftDeletes;

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
        'submission_token',
        'open_monthly_payment', // ✅ add this
        'is_unpriced_contract', 'first_due_date', 'ended_at', 'total_agreed_at', 'total_agreed_by',
    ];

    protected $casts = [
        'is_open_contract'      => 'boolean',
        'is_unpriced_contract'  => 'boolean',
        'first_due_date'        => 'date',
        'ended_at'             => 'date',
        'total_agreed_at'      => 'datetime',
        'open_monthly_payment'  => 'decimal:2',
        'total_cost'            => 'decimal:2',
        'downpayment'           => 'decimal:2',
        'balance'               => 'decimal:2',
        'start_date'            => 'date',
    ];

    protected $attributes = [
        'status' => self::STATUS_PENDING,
        'is_open_contract' => false,
    ];

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

    public function visit() { return $this->belongsTo(Visit::class)->withTrashed(); }
    public function patient() { return $this->belongsTo(Patient::class)->withTrashed(); }
    public function service() { return $this->belongsTo(Service::class)->withTrashed(); }
    public function payments() { return $this->hasMany(InstallmentPayment::class, 'installment_plan_id'); }

    public function hasUnknownTotal(): bool
    {
        return (bool) $this->is_unpriced_contract && $this->total_cost === null;
    }
}
