<?php

namespace App\Imports;

use App\Models\Patient;
use Carbon\Carbon;
use DateTimeInterface;
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

        $birthdate = $this->parseDateToYmd($row['birthdate'] ?? null);

        return new Patient([
            'last_name'      => $last,
            'first_name'     => $first,
            'middle_name'    => $this->nullIfBlank($row['middle_name'] ?? null),
            'gender'         => $this->nullIfBlank($row['gender'] ?? null),

            // ✅ create-form aligned
            'birthdate'      => $birthdate,
            'nickname'       => $this->nullIfBlank($row['nickname'] ?? null),
            'address'        => $this->nullIfBlank($row['address'] ?? null),
            'occupation'     => $this->nullIfBlank($row['occupation'] ?? null),
            'contact_number' => isset($row['contact_number']) ? (string)$row['contact_number'] : null,

            // ✅ email optional for now
            'email'          => $this->nullIfBlank($row['email'] ?? null),

            // ✅ optional extras (safe if columns exist in DB)
            'home_no'        => $this->nullIfBlank($row['home_no'] ?? null),
            'office_no'      => $this->nullIfBlank($row['office_no'] ?? null),
            'fax_no'         => $this->nullIfBlank($row['fax_no'] ?? null),
            'dental_insurance'=> $this->nullIfBlank($row['dental_insurance'] ?? null),
            'effective_date' => $this->parseDateToYmd($row['effective_date'] ?? null),
            'notes'          => $this->nullIfBlank($row['notes'] ?? null),

            'is_minor'       => $this->toBool($row['is_minor'] ?? null),
            'guardian_name'  => $this->nullIfBlank($row['guardian_name'] ?? null),
            'guardian_occupation' => $this->nullIfBlank($row['guardian_occupation'] ?? null),
        ]);
    }

    private function nullIfBlank($v): ?string
    {
        $v = trim((string)($v ?? ''));
        return $v === '' ? null : $v;
    }

    private function toBool($v): ?int
    {
        if ($v === null || $v === '') return null;
        $s = strtolower(trim((string)$v));
        if (in_array($s, ['1','true','yes','y'], true)) return 1;
        if (in_array($s, ['0','false','no','n'], true)) return 0;
        if (is_numeric($v)) return ((int)$v) ? 1 : 0;
        return null;
    }

    private function parseDateToYmd($value): ?string
    {
        if ($value === null || $value === '') return null;

        // Excel numeric date
        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->toDateString();
        }

        // Already a date object
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        $v = trim((string)$value);

        // Try strict known formats first
        foreach ([
            'Y-m-d',     // 2008-11-22
            'd/m/Y',     // 22/11/2008  ✅ your file
            'm/d/Y',
            'd-m-Y',
            'm-d-Y',
            'Y/m/d',
            'd.m.Y',
            'm.d.Y',
        ] as $fmt) {
            try {
                return Carbon::createFromFormat($fmt, $v)->toDateString();
            } catch (\Throwable $e) {
                // continue
            }
        }

        // Fallback (may still fail)
        try {
            return Carbon::parse($v)->toDateString();
        } catch (\Throwable $e) {
            return null; // or throw if you want to hard-fail invalid dates
        }
    }
}
