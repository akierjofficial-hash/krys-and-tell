<?php

namespace App\Exports;

use App\Models\Patient;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PatientsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Patient::select(
            'id',
            'last_name',
            'first_name',
            'middle_name',
            'gender',
            'birthdate',
            'contact_number',
            'created_at'
        )->get();
    }

    public function headings(): array
    {
        return [
            'id',
            'last_name',
            'first_name',
            'middle_name',
            'gender',
            'birthdate',
            'contact_number',
            'created_at'
        ];
    }
}
