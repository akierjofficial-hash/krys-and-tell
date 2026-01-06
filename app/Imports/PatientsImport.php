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
        $last  = trim((string)($row['last_name'] ?? ''));
        $first = trim((string)($row['first_name'] ?? ''));

        if ($last === '' || $first === '') {
            return null;
        }

        $birthdate = $this->parseBirthdate($row['birthdate'] ?? null);
        $contact   = $this->normalizeContact($row['contact_number'] ?? null);

        return new Patient([
            'last_name'      => $last,
            'first_name'     => $first,
            'middle_name'    => trim((string)($row['middle_name'] ?? '')) ?: null,
            'gender'         => trim((string)($row['gender'] ?? '')) ?: null,
            'birthdate'      => $birthdate,
            'contact_number' => $contact,
            'address'        => trim((string)($row['address'] ?? '')) ?: null,
        ]);
    }

    private function parseBirthdate($value): ?string
    {
        if ($value === null || $value === '') return null;

        // Excel date number
        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->toDateString();
        }

        $s = trim((string)$value);
        if ($s === '') return null;

        // Prefer mm/dd (your requested format), but accept common fallbacks too
        $formats = ['m/d/Y', 'm/d/y', 'Y-m-d', 'm-d-Y', 'm-d-y', 'd/m/Y', 'd/m/y'];

        foreach ($formats as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $s)->format('Y-m-d');
            } catch (\Throwable $e) {
                // try next
            }
        }

        // last resort
        try {
            return Carbon::parse($s)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizeContact($value): ?string
    {
        if ($value === null || $value === '') return null;

        // If Excel gave a number, convert safely to digits (no decimals)
        if (is_int($value) || is_float($value)) {
            $value = number_format($value, 0, '', '');
        }

        $s = trim((string)$value);
        if ($s === '') return null;

        // keep digits only
        $digits = preg_replace('/\D+/', '', $s);
        if ($digits === '') return null;

        // common PH case: lost the leading 0 -> 10 digits
        if (strlen($digits) === 10) {
            $digits = '0' . $digits;
        }

        return $digits;
    }
}
