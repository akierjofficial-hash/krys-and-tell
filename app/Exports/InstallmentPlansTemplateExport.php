<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class InstallmentPlansTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'visit_id',
            'total_cost',
            'downpayment',
            'is_open_contract',
            'months',
            'start_date',
        ];
    }

    public function array(): array
    {
        return [[
            '1203',
            '18000',
            '3000',
            '0',
            '6',
            '01/15/2026',
        ]];
    }
}
