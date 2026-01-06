<?php

namespace App\Imports;

use App\Models\Patient;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use DateTimeInterface;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class PatientsImport implements ToModel, WithHeadingRow, SkipsEmptyRows
{
    public function model(array $row)
    {
        // Required fields
        $last  = trim((string)($row['last_name'] ?? ''));
        $first = trim((string)($row['first_name'] ?? ''));

        if ($last === '' || $first === '') {
            return null;
        }

        $birthdate = $this->parseBirthdate($row['birthdate'] ?? null);

        return new Patient([
            'last_name'      => $last,
            'first_name'     => $first,
            'middle_name'    => $this->nullIfBlank($row['middle_name'] ?? null),
            'gender'         => $this->nullIfBlank($row['gender'] ?? null),
            'birthdate'      => $birthdate, // Y-m-d or null
            'contact_number' => $this->normalizePhone($row['contact_number'] ?? null),
            'address'        => $this->nullIfBlank($row['address'] ?? null),
        ]);
    }

    private function parseBirthdate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Excel can give a DateTimeInterface
        if ($value instanceof DateTimeInterface) {
            return Carbon::instance($value)->toDateString();
        }

        // Excel numeric date serial
        if (is_numeric($value)) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->toDateString();
            } catch (\Throwable $e) {
                return null;
            }
        }

        // String dates
        $str = trim((string)$value);
        if ($str === '') return null;

        // Try common formats first (including DD/MM/YYYY)
        $formats = [
            'd/m/Y', 'd-m-Y', 'd.m.Y',
            'm/d/Y', 'm-d-Y', 'm.d.Y',
            'Y-m-d', 'Y/m/d', 'Y.m.d',
            'd/m/Y H:i:s', 'm/d/Y H:i:s', 'Y-m-d H:i:s',
        ];

        foreach ($formats as $fmt) {
            try {
                $dt = Carbon::createFromFormat($fmt, $str);
                if ($dt !== false) {
                    return $dt->toDateString();
                }
            } catch (\Throwable $e) {
                // try next format
            }
        }

        // Last resort: Carbon::parse (can still fail)
        try {
            return Carbon::parse($str)->toDateString();
        } catch (InvalidFormatException $e) {
            return null; // don't crash import
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function normalizePhone($value): ?string
    {
        if ($value === null || $value === '') return null;

        // If numeric, Excel might convert to float/scientific notation
        if (is_numeric($value)) {
            // Format as whole number without decimals/scientific notation
            $value = rtrim(rtrim(sprintf('%.0f', (float)$value), '0'), '.');
        }

        $str = trim((string)$value);
        return $str === '' ? null : $str;
    }

    private function nullIfBlank($value): ?string
    {
        $str = trim((string)($value ?? ''));
        return $str === '' ? null : $str;
    }
}
