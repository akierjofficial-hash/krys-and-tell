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
        'related_visit_id',
        'related_installment_plan_id',
    ];

    public function visit()
    {
        return $this->belongsTo(Visit::class)->withTrashed();
    }

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function relatedVisit()
    {
        return $this->belongsTo(Visit::class, 'related_visit_id')->withTrashed();
    }

    public function relatedInstallmentPlan()
    {
        return $this->belongsTo(InstallmentPlan::class, 'related_installment_plan_id')->withTrashed();
    }
}
