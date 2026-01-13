<?php

namespace App\Imports;

use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class InstallmentPaymentsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;

    /** @var string[] */
    public array $errors = [];

    public function __construct(private InstallmentPlan $plan)
    {
    }

    public function collection(Collection $rows)
    {
        $this->plan->loadMissing(['visit', 'service']);

        foreach ($rows as $i => $row) {
            $rowNo = $i + 2;

            DB::transaction(function () use ($row, $rowNo) {
                $month = $row['month_number'] ?? null;
                if ($month === null || $month === '' || !is_numeric($month) || (int)$month < 0) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: month_number is required and must be >= 0.";
                    return;
                }
                $month = (int)$month;

                // If not open contract, validate month range (except DP=0)
                if (!(bool)($this->plan->is_open_contract ?? false)) {
                    $max = (int)($this->plan->months ?? 0);
                    if ($month !== 0 && ($month < 1 || $month > $max)) {
                        $this->skipped++;
                        $this->errors[] = "Row {$rowNo}: month_number {$month} is out of range (1..{$max}).";
                        return;
                    }
                }

                $amount = $this->toMoney($row['amount'] ?? null);
                if ($amount === null || (float)$amount < 0) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: amount is required and must be >= 0.";
                    return;
                }

                $paymentDate = $this->parseDate($row['payment_date'] ?? null);
                if (!$paymentDate) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: payment_date is required (mm/dd/yy, mm/dd/yyyy, yyyy-mm-dd, or Excel date).";
                    return;
                }

                $method = $this->nullIfEmpty($row['method'] ?? null);
                $notes  = $this->nullIfEmpty($row['notes'] ?? null);

                $overwrite = $this->toBool($row['overwrite'] ?? null) ?? false;

                $existing = InstallmentPayment::where('installment_plan_id', $this->plan->id)
                    ->where('month_number', $month)
                    ->first();

                // Determine visit_id:
                // - If month 0: link to plan->visit_id by default
                // - If month >=1: create a new visit like the manual payment flow if no visit_id is provided
                $visitIdCell = $row['visit_id'] ?? null;
                $visitId = (is_numeric($visitIdCell) && (int)$visitIdCell > 0) ? (int)$visitIdCell : null;

                if ($month === 0) {
                    $visitId = $visitId ?: (int)$this->plan->visit_id;

                    // If they import DP, also keep plan.downpayment aligned (same as your update logic)
                    $this->plan->downpayment = (float)$amount;
                    $this->plan->save();

                    if (!$notes) $notes = 'Downpayment';
                } else {
                    if (!$visitId) {
                        $visitId = $this->createVisitForPayment($paymentDate, $notes, $month);
                    }
                }

                if ($existing && !$overwrite) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: month_number {$month} already exists for this plan (set overwrite=1 to update).";
                    return;
                }

                if ($existing) {
                    $existing->update([
                        'visit_id'      => $visitId,
                        'amount'        => (float)$amount,
                        'method'        => $method ?: $existing->method,
                        'payment_date'  => $paymentDate,
                        'notes'         => $notes,
                    ]);

                    // Keep linked visit in sync (like your controller update)
                    if (!empty($existing->visit_id)) {
                        $v = Visit::find($existing->visit_id);
                        if ($v) {
                            $v->visit_date = $paymentDate;
                            if ($notes) $v->notes = $notes;
                            $v->save();
                        }
                    }

                    $this->updated++;
                } else {
                    InstallmentPayment::create([
                        'installment_plan_id' => $this->plan->id,
                        'visit_id'            => $visitId,
                        'month_number'        => $month,
                        'amount'              => (float)$amount,
                        'method'              => $method,
                        'payment_date'        => $paymentDate,
                        'notes'               => $notes,
                    ]);

                    $this->created++;
                }

                // Recompute plan after each row (safe + simple; can be optimized later)
                $this->recomputePlan($this->plan);
            });
        }
    }

    private function createVisitForPayment(string $paymentDate, ?string $notes, int $month): int
    {
        $baseVisit = $this->plan->visit;

        $visitNotes = trim((string)($notes ?? ''));
        if ($visitNotes === '') {
            $svc = $this->plan->service?->name;
            $visitNotes = $svc ? "Installment payment (Month {$month}) - {$svc}" : "Installment payment (Month {$month})";
        }

        $visit = Visit::create([
            'patient_id'   => $this->plan->patient_id,
            'doctor_id'    => $baseVisit?->doctor_id,
            'dentist_name' => $baseVisit?->dentist_name,
            'visit_date'   => $paymentDate,
            'status'       => 'completed',
            'notes'        => $visitNotes,
            'price'        => null,
        ]);

        if (!empty($this->plan->service_id)) {
            $visit->procedures()->create([
                'service_id'   => $this->plan->service_id,
                'tooth_number' => null,
                'surface'      => null,
                'shade'        => null,
                'notes'        => null,
                'price'        => 0,
            ]);
        }

        return (int)$visit->id;
    }

    private function recomputePlan(InstallmentPlan $plan): void
    {
        $total = (float)($plan->total_cost ?? 0);

        $paymentsTotal = (float)InstallmentPayment::where('installment_plan_id', $plan->id)->sum('amount');

        $hasDp = InstallmentPayment::where('installment_plan_id', $plan->id)
            ->where('month_number', 0)
            ->exists();

        $paid = $paymentsTotal + ($hasDp ? 0 : (float)($plan->downpayment ?? 0));

        $balance = max(0, $total - $paid);
        $status  = $balance <= 0 ? 'Fully Paid' : 'Partially Paid';

        $plan->balance = $balance;
        $plan->status  = $status;
        $plan->save();
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

    private function nullIfEmpty($v): ?string
    {
        $s = trim((string)($v ?? ''));
        return $s === '' ? null : $s;
    }

    private function toBool($value): ?bool
    {
        if ($value === null || $value === '') return null;
        if (is_bool($value)) return $value;

        $s = strtolower(trim((string)$value));
        if (in_array($s, ['1','true','yes','y','on'], true)) return true;
        if (in_array($s, ['0','false','no','n','off'], true)) return false;

        return null;
    }
}
