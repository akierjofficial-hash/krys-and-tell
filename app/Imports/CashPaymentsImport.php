<?php

namespace App\Imports;

use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class CashPaymentsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $inserted = 0;
    public int $skipped = 0;
    /** @var string[] */
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $rowNo = $i + 2; // heading is row 1

            try {
                $visit = $this->resolveOrCreateVisit($row);
                if (!$visit) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: Missing/invalid visit reference (use visit_id OR patient_id+visit_date OR patient name+visit_date).";
                    continue;
                }

                if (InstallmentPlan::where('visit_id', $visit->id)->exists()) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: Visit #{$visit->id} is under an installment plan (skipped).";
                    continue;
                }

                $amount = $this->toMoney($row['amount'] ?? null);
                if ($amount === null) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: amount is required and must be numeric.";
                    continue;
                }

                $paymentDate = $this->parseDate($row['payment_date'] ?? null);
                if (!$paymentDate) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: payment_date is required (mm/dd/yy, mm/dd/yyyy, yyyy-mm-dd, or Excel date).";
                    continue;
                }

                $method = $this->normalizeMethod($row['method'] ?? null);
                if (!$method) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: method is required (Cash, GCash, Card, Bank Transfer).";
                    continue;
                }

                $notes = trim((string)($row['notes'] ?? ''));
                $notes = $notes !== '' ? $notes : null;

                $visitTotal = $this->toMoney($row['visit_total_due'] ?? null);
                if ($visitTotal !== null) {
                    $visit->price = $visitTotal;
                    $visit->save();
                }

                $exists = Payment::query()
                    ->where('visit_id', $visit->id)
                    ->whereDate('payment_date', $paymentDate)
                    ->where('amount', $amount)
                    ->where('method', $method)
                    ->where(function ($q) use ($notes) {
                        $notes === null ? $q->whereNull('notes') : $q->where('notes', $notes);
                    })
                    ->exists();

                if ($exists) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: duplicate payment detected for Visit #{$visit->id} (skipped).";
                    continue;
                }

                Payment::create([
                    'visit_id'     => $visit->id,
                    'amount'       => $amount,
                    'method'       => $method,
                    'payment_date' => $paymentDate,
                    'notes'        => $notes,
                ]);

                $this->updateVisitStatusBasedOnPayments($visit);

                $this->inserted++;
            } catch (\Throwable $e) {
                $this->skipped++;
                $this->errors[] = "Row {$rowNo}: {$e->getMessage()}";
            }
        }
    }

    private function resolveOrCreateVisit($row): ?Visit
    {
        $visitId = $row['visit_id'] ?? null;
        if ($visitId !== null && $visitId !== '' && is_numeric($visitId)) {
            return Visit::find((int)$visitId);
        }

        $patientId = $row['patient_id'] ?? null;
        $visitDate = $this->parseDate($row['visit_date'] ?? null);

        if ($patientId !== null && $patientId !== '' && is_numeric($patientId) && $visitDate) {
            $patientId = (int)$patientId;

            $visit = Visit::where('patient_id', $patientId)
                ->whereDate('visit_date', $visitDate)
                ->orderByDesc('id')
                ->first();

            if ($visit) return $visit;

            if (Patient::find($patientId)) {
                return Visit::create([
                    'patient_id' => $patientId,
                    'visit_date' => $visitDate,
                    'status'     => 'partial',
                    'notes'      => 'Imported visit (auto-created)',
                ]);
            }
        }

        $last  = trim((string)($row['patient_last_name'] ?? ''));
        $first = trim((string)($row['patient_first_name'] ?? ''));

        if ($last !== '' && $first !== '' && $visitDate) {
            $patient = Patient::query()
                ->whereRaw('LOWER(last_name) = ?', [mb_strtolower($last)])
                ->whereRaw('LOWER(first_name) = ?', [mb_strtolower($first)])
                ->orderByDesc('id')
                ->first();

            if ($patient) {
                $visit = Visit::where('patient_id', $patient->id)
                    ->whereDate('visit_date', $visitDate)
                    ->orderByDesc('id')
                    ->first();

                if ($visit) return $visit;

                return Visit::create([
                    'patient_id' => $patient->id,
                    'visit_date' => $visitDate,
                    'status'     => 'partial',
                    'notes'      => 'Imported visit (auto-created)',
                ]);
            }
        }

        return null;
    }

    private function parseDate($value): ?string
    {
        if ($value === null || $value === '') return null;

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject($value))->toDateString();
        }

        $s = trim((string)$value);
        if ($s === '') return null;

        $formats = ['m/d/Y', 'm/d/y', 'Y-m-d', 'm-d-Y', 'm-d-y', 'd/m/Y', 'd/m/y'];
        foreach ($formats as $fmt) {
            try { return Carbon::createFromFormat($fmt, $s)->format('Y-m-d'); }
            catch (\Throwable $e) {}
        }

        try { return Carbon::parse($s)->toDateString(); }
        catch (\Throwable $e) { return null; }
    }

    private function toMoney($value): ?float
    {
        if ($value === null || $value === '') return null;
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '') return null;
            $value = preg_replace('/[^0-9.\-]/', '', $value);
        }
        if (!is_numeric($value)) return null;
        return (float)$value;
    }

    private function normalizeMethod($value): ?string
    {
        $s = trim((string)($value ?? ''));
        if ($s === '') return null;

        $n = mb_strtolower($s);
        $n = str_replace(['_', '-'], ' ', $n);
        $n = preg_replace('/\s+/', ' ', $n);

        return match ($n) {
            'cash' => 'Cash',
            'gcash', 'g cash' => 'GCash',
            'card', 'credit card', 'debit card' => 'Card',
            'bank transfer', 'bank', 'transfer' => 'Bank Transfer',
            default => ucwords($n),
        };
    }

    private function visitDue(Visit $visit): float
    {
        if ($visit->price !== null) return (float)$visit->price;
        return (float)$visit->procedures()->sum('price');
    }

    private function visitPaid(Visit $visit): float
    {
        return (float)Payment::where('visit_id', $visit->id)->sum('amount');
    }

    private function updateVisitStatusBasedOnPayments(Visit $visit): void
    {
        $due  = $this->visitDue($visit);
        $paid = $this->visitPaid($visit);

        $visit->update([
            'status' => ($due > 0 && $paid >= $due) ? 'completed' : 'partial',
        ]);
    }
}
