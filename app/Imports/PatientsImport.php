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

        return new Patient([
            'last_name'      => $last,
            'first_name'     => $first,
            'middle_name'    => trim((string)($row['middle_name'] ?? '')) ?: null,
            'gender'         => trim((string)($row['gender'] ?? '')) ?: null,
            'birthdate'      => $birthdate,

            // NOTE: best practice is to format this column as TEXT in Excel
            'contact_number' => isset($row['contact_number']) ? $this->safeString($row['contact_number']) : null,

            'address'        => trim((string)($row['address'] ?? '')) ?: null,
        ]);
    }

    private function safeString($value): ?string
    {
        if ($value === null) return null;

        // If Excel gives a float/int, convert without scientific notation
        if (is_numeric($value)) {
            // number_format keeps big numbers readable (no commas)
            return number_format((float)$value, 0, '', '');
        }

        return trim((string)$value) ?: null;
    }

    private function parseBirthdate($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Excel serial date
        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->toDateString();
        }

        $s = trim((string)$value);
        if ($s === '') return null;

        // Normalize separators
        $s = str_replace(['.', '-'], '/', $s);

        // If it's already ISO-ish after normalization: YYYY/MM/DD
        if (preg_match('/^\d{4}\/\d{1,2}\/\d{1,2}$/', $s)) {
            return Carbon::createFromFormat('Y/m/d', $s)->toDateString();
        }

        // Handle slashed dates like 11/22/2008 or 22/11/2008
        if (preg_match('/^\d{1,2}\/\d{1,2}\/\d{2,4}$/', $s)) {
            [$a, $b, $y] = explode('/', $s);
            $a = (int)$a; // first part
            $b = (int)$b; // second part

            $yearFmt = (strlen($y) === 2) ? 'y' : 'Y';

            // Auto-detect:
            // - if first part > 12 => DD/MM
            // - if second part > 12 => MM/DD
            // - ambiguous (both <= 12) => DEFAULT to MM/DD (your preference)
            if ($a > 12 && $b <= 12) {
                $fmt = "d/m/{$yearFmt}";
            } elseif ($b > 12 && $a <= 12) {
                $fmt = "m/d/{$yearFmt}";
            } else {
                $fmt = "m/d/{$yearFmt}"; // default preference
            }

            try {
                return Carbon::createFromFormat($fmt, $s)->toDateString();
            } catch (\Throwable $e) {
                // fall through to last-resort parsing
            }
        }

        // Last resort (e.g. "Nov 22, 2008")
        try {
            return Carbon::parse($s)->toDateString();
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException("Invalid birthdate format: '{$s}'. Use MM/DD/YY (preferred) or YYYY-MM-DD.");
        }
    }
}
