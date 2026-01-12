<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class CashPaymentsTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'visit_id',
            'patient_id',
            'patient_last_name',
            'patient_first_name',
            'visit_date',
            'visit_total_due',
            'amount',
            'method',
            'payment_date',
            'notes',
        ];
    }

    public function array(): array
    {
        return [[ '', '', '', '', '', '', '', '', '', '' ]];
    }
}
