<?php

namespace App\Imports;

use App\Models\InstallmentPlan;
use App\Models\InstallmentPayment;
use App\Models\Visit;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class InstallmentPlansImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public int $created = 0;
    public int $updated = 0;
    public int $skipped = 0;

    /** @var string[] */
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $rowNo = $i + 2;

            DB::transaction(function () use ($row, $rowNo) {
                $visitId = $row['visit_id'] ?? null;
                if ($visitId === null || $visitId === '' || !is_numeric($visitId)) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: visit_id is required and must be a number.";
                    return;
                }

                $visit = Visit::with(['procedures.service'])->find((int)$visitId);
                if (!$visit) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: visit_id {$visitId} not found.";
                    return;
                }

                // Optional update path if user adds "installment_plan_id" column later
                $planId = $row['installment_plan_id'] ?? null;
                $plan = null;

                if ($planId !== null && $planId !== '' && is_numeric($planId)) {
                    $plan = InstallmentPlan::find((int)$planId);
                    if (!$plan) {
                        $this->skipped++;
                        $this->errors[] = "Row {$rowNo}: installment_plan_id {$planId} not found.";
                        return;
                    }
                } else {
                    // Prevent duplicates (one plan per visit)
                    $existing = InstallmentPlan::where('visit_id', (int)$visitId)->first();
                    if ($existing) {
                        $this->skipped++;
                        $this->errors[] = "Row {$rowNo}: plan already exists for visit_id {$visitId} (skipped).";
                        return;
                    }
                }

                $totalCost = $this->toMoney($row['total_cost'] ?? null);
                if ($totalCost === null) {
                    // fallback: sum procedure prices if not provided
                    $totalCost = (float)$visit->procedures->sum('price');
                }
                if ($totalCost === null) $totalCost = 0.0;

                $down = $this->toMoney($row['downpayment'] ?? null);
                $down = $down === null ? 0.0 : (float)$down;

                if ($down > $totalCost + 0.0001) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: downpayment cannot exceed total_cost.";
                    return;
                }

                $isOpen = $this->toBool($row['is_open_contract'] ?? null) ?? false;

                $monthsVal = $row['months'] ?? null;
                $months = 0;

                if ($isOpen) {
                    // open contract: months can be blank
                    $months = is_numeric($monthsVal) ? max(0, (int)$monthsVal) : 0;
                } else {
                    if ($monthsVal === null || $monthsVal === '' || !is_numeric($monthsVal) || (int)$monthsVal < 1) {
                        $this->skipped++;
                        $this->errors[] = "Row {$rowNo}: months is required (>=1) when is_open_contract = 0.";
                        return;
                    }
                    $months = (int)$monthsVal;
                }

                $startDate = $this->parseDate($row['start_date'] ?? null);
                if (!$startDate) {
                    $this->skipped++;
                    $this->errors[] = "Row {$rowNo}: start_date is required (mm/dd/yy, mm/dd/yyyy, yyyy-mm-dd, or Excel date).";
                    return;
                }

                $serviceId = optional($visit->procedures->first()?->service)->id;

                $payload = [
                    'visit_id'         => $visit->id,
                    'patient_id'       => $visit->patient_id,
                    'service_id'       => $serviceId,
                    'total_cost'       => (float)$totalCost,
                    'downpayment'      => (float)$down,
                    'is_open_contract' => (bool)$isOpen,
                    'months'           => $months,
                    'start_date'       => $startDate,
                ];

                if ($plan) {
                    $plan->update($payload);
                    $this->updated++;
                } else {
                    $plan = InstallmentPlan::create($payload);
                    $this->created++;
                }

                // Ensure downpayment record exists as month 0 (matching your controller logic)
                $this->ensureDownpaymentPayment($plan);

                // Recompute balance/status
                $this->recomputePlan($plan);
            });
        }
    }

    private function ensureDownpaymentPayment(InstallmentPlan $plan): void
    {
        $down = (float)($plan->downpayment ?? 0);
        if ($down <= 0) return;

        $exists = InstallmentPayment::where('installment_plan_id', $plan->id)
            ->where('month_number', 0)
            ->exists();

        if ($exists) return;

        InstallmentPayment::create([
            'installment_plan_id' => $plan->id,
            'visit_id'            => $plan->visit_id,
            'month_number'        => 0,
            'amount'              => $down,
            'method'              => 'Cash',
            'payment_date'        => $plan->start_date,
            'notes'               => 'Downpayment',
        ]);
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
