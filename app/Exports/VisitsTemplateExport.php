<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class VisitsTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'visit_group',
            'visit_id',
            'patient_id',
            'patient_last_name',
            'patient_first_name',
            'doctor_id',
            'dentist_name',
            'visit_date',
            'visit_notes',
            'visit_total_due',
            'service_id',
            'service_name',
            'tooth_number',
            'surface',
            'shade',
            'procedure_price',
            'procedure_notes',
        ];
    }

    public function array(): array
    {
        return [[
            'V-0001',
            '',
            '',
            '',
            '',
            '',
            '',
            '01/12/2026',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
        ]];
    }
}
