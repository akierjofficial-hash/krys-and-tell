<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            $now = Carbon::now();

            $plans = DB::table('installment_plans')
                ->select('id','downpayment','months','start_date','visit_id','total_cost')
                ->orderBy('id')
                ->get();

            foreach ($plans as $plan) {
                $down = (float)($plan->downpayment ?? 0);
                if ($down <= 0) {
                    continue;
                }

                // already migrated?
                $hasMonth0 = DB::table('installment_payments')
                    ->where('installment_plan_id', $plan->id)
                    ->where('month_number', 0)
                    ->exists();

                if ($hasMonth0) {
                    // still recompute balance/status for safety
                    $paid = (float)DB::table('installment_payments')
                        ->where('installment_plan_id', $plan->id)
                        ->sum('amount');

                    $balance = max(0, (float)$plan->total_cost - $paid);
                    DB::table('installment_plans')->where('id', $plan->id)->update([
                        'balance' => $balance,
                        'status'  => $balance <= 0 ? 'Fully Paid' : 'Partially Paid',
                        'updated_at' => $now,
                    ]);

                    continue;
                }

                // Try to find old downpayment (month 1) using strong signal: notes contains "downpayment"
                $dp = DB::table('installment_payments')
                    ->where('installment_plan_id', $plan->id)
                    ->where('month_number', 1)
                    ->where(function ($q) {
                        $q->where('notes', 'like', '%downpayment%')
                          ->orWhere('notes', 'like', '%Downpayment%');
                    })
                    ->orderBy('id')
                    ->first();

                if ($dp) {
                    // Move month 1 -> month 0
                    DB::table('installment_payments')->where('id', $dp->id)->update([
                        'month_number' => 0,
                        'notes'        => $dp->notes ?: 'Downpayment',
                        'method'       => $dp->method ?: 'Cash',
                        'payment_date' => $dp->payment_date ?: $plan->start_date,
                        'visit_id'     => $dp->visit_id ?: $plan->visit_id,
                        'updated_at'   => $now,
                    ]);

                    // Shift month 2->1, 3->2, ...
                    DB::table('installment_payments')
                        ->where('installment_plan_id', $plan->id)
                        ->whereNotNull('month_number')
                        ->where('month_number', '>', 1)
                        ->decrement('month_number', 1);

                    // Convert old meaning of months (included DP) -> new meaning (monthly months only)
                    $oldMonths = (int)($plan->months ?? 0);
                    if ($oldMonths > 1) {
                        DB::table('installment_plans')->where('id', $plan->id)->update([
                            'months' => $oldMonths - 1,
                            'updated_at' => $now,
                        ]);
                        $plan->months = $oldMonths - 1;
                    }
                } else {
                    // If we can't confidently detect old DP record, create month 0 DP record only (no shifting)
                    DB::table('installment_payments')->insert([
                        'installment_plan_id' => $plan->id,
                        'visit_id'            => $plan->visit_id,
                        'month_number'        => 0,
                        'amount'              => $down,
                        'method'              => 'Cash',
                        'payment_date'        => $plan->start_date,
                        'notes'               => 'Downpayment',
                        'created_at'          => $now,
                        'updated_at'          => $now,
                    ]);
                }

                // Recompute plan balance/status
                $paid = (float)DB::table('installment_payments')
                    ->where('installment_plan_id', $plan->id)
                    ->sum('amount');

                $balance = max(0, (float)$plan->total_cost - $paid);

                DB::table('installment_plans')->where('id', $plan->id)->update([
                    'balance' => $balance,
                    'status'  => $balance <= 0 ? 'Fully Paid' : 'Partially Paid',
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        // Intentionally left blank (data migration).
    }
};
