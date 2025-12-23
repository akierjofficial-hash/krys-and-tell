<?php

namespace App\Imports;

use App\Models\Patient;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;

class PatientsImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        // Skip if required fields are missing (extra safety)
        $last = trim((string)($row['last_name'] ?? ''));
        $first = trim((string)($row['first_name'] ?? ''));

        if ($last === '' || $first === '') {
            return null;
        }

        // Birthdate can be Excel date (numeric) or string
        $birthdate = null;
        if (!empty($row['birthdate'])) {
            if (is_numeric($row['birthdate'])) {
                $birthdate = Carbon::instance(ExcelDate::excelToDateTimeObject($row['birthdate']))->toDateString();
            } else {
                $birthdate = Carbon::parse($row['birthdate'])->toDateString();
            }
        }

        return new Patient([
            'last_name'      => $last,
            'first_name'     => $first,
            'middle_name'    => trim((string)($row['middle_name'] ?? '')) ?: null,
            'gender'         => trim((string)($row['gender'] ?? '')) ?: null,
            'birthdate'      => $birthdate,
            'contact_number' => isset($row['contact_number']) ? (string)$row['contact_number'] : null,
            'address'        => trim((string)($row['address'] ?? '')) ?: null, // IMPORTANT: header must be "address"
        ]);
    }
}
