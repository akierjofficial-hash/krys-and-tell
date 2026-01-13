<?php

namespace App\Exports;

use App\Models\InstallmentPlan;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InstallmentPaymentsTemplateExport implements FromArray, WithHeadings
{
    public function __construct(private ?InstallmentPlan $plan = null)
    {
    }

    public function headings(): array
    {
        return [
            'month_number',
            'amount',
            'method',
            'payment_date',
            'notes',
            'visit_id',
            'overwrite',
        ];
    }

    public function array(): array
    {
        $date = '02/15/2026';
        if ($this->plan?->start_date) {
            try {
                $date = \Carbon\Carbon::parse($this->plan->start_date)->addMonth()->format('m/d/Y');
            } catch (\Throwable $e) {}
        }

        return [[
            '1',
            '2500',
            'Cash',
            $date,
            'Monthly payment',
            '',
            '0',
        ]];
    }
}
