<?php

namespace App\Services;

use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\CarbonImmutable;

/** Clinic-wide, read-only questions. No model output is used as a query or a total. */
class StaffClinicAssistant
{
    private const LIST_LIMIT = 10;

    public function __construct(private readonly FinancialService $finance) {}

    public function intent(string $question, bool $hasPatient): ?string
    {
        $text = mb_strtolower($question);
        $clinic = preg_match('/\b(all|clinic|patients|across|overall)\b/u', $text);

        if (preg_match('/\bpatients\b.*\b(outstanding|balances?|owe|owing|unpaid)\b|\b(outstanding|unpaid)\b.*\bpatients\b|\b(outstanding|unpaid)\b.*\bbalances?\b/u', $text)
            || ((!$hasPatient || $clinic) && preg_match('/\b(clinic|overall|total)\b.*\bbalances?\b/u', $text))) return 'outstanding';
        if (preg_match('/\binstallments?\b.*\b(due|overdue)\b|\b(due|overdue)\b.*\binstallments?\b/u', $text) && (!$hasPatient || $clinic)) return 'installments';
        if (preg_match('/\b(payments?|receipts?|collections?)\b.*\b(received|collected|collect)\b|\b(received|collected|collect)\b.*\b(payments?|receipts?)\b|\b(clinic|we)\b.*\b(collect|collected)\b/u', $text)
            && preg_match('/\b(today|this week)\b/u', $text)) return 'collections';
        if (preg_match('/\bvisits?\b/u', $text) && preg_match('/\b(how many|count|number|date range|between)\b/u', $text)
            && (!$hasPatient || $clinic || preg_match('/\b(date range|between|selected date)\b/u', $text))) return 'visits';

        return null;
    }

    public function answer(string $intent, string $question, ?string $from = null, ?string $to = null): array
    {
        $today = CarbonImmutable::now(config('app.timezone'))->toDateString();

        return match ($intent) {
            'outstanding' => $this->outstanding($today),
            'installments' => $this->installments($today),
            'collections' => $this->collections($question, $today),
            'visits' => $this->visits($from, $to),
        };
    }

    private function outstanding(string $today): array
    {
        $overview = $this->balanceOverview();
        $count = $overview['count'];
        $total = $overview['total'];
        $unresolved = $overview['incomplete_count'];
        $links = collect($overview['review_items'])->concat($overview['known_items'])->take(self::LIST_LIMIT)
            ->map(fn ($item) => ['label' => $item['link_label'], 'url' => $item['url']])->all();
        $message = 'Current recorded balances, checked '.$today.' ('.config('app.timezone').'): '.$count.' patients have a calculable outstanding balance totaling '.$this->money($total).'.'
            .$this->limited($count + $unresolved).' Financed treatments are counted through their plan once.';
        if ($unresolved) $message .= ' Incomplete: '.$unresolved.' patient balance(s) include mixed charges, mismatched plan links, an unreceipted downpayment, or a monthly contract with no agreed total. Their final balances are excluded from this total; review the linked records.';
        if ($overview['assumed_downpayments']) $message .= ' '.$overview['assumed_downpayments'].' plan(s) count an entered downpayment without a receipt under the existing balance rule; this is not proof of collection.';
        return $this->result($message, $count, $total, $today, $today, $links);
    }

    /** Shared definition for the dashboard and the clinic-wide answer; never changes source records. */
    public function balanceOverview(): array
    {
        $count = 0;
        $total = 0.0;
        $reviewCount = 0;
        $knownItems = [];
        $reviewItems = [];
        $assumedDownpayments = 0;
        Patient::query()->orderBy('id')->chunkById(100, function ($patients) use (&$count, &$total, &$reviewCount, &$knownItems, &$reviewItems, &$assumedDownpayments) {
            $ids = $patients->pluck('id');
            $visits = Visit::with(['procedures', 'payments', 'installmentPlan'])->whereIn('patient_id', $ids)->get()->groupBy('patient_id');
            $plans = InstallmentPlan::with('payments')->whereIn('patient_id', $ids)->get()->groupBy('patient_id');

            foreach ($patients as $patient) {
                $reviewVisit = null;
                $ordinary = ($visits->get($patient->id) ?? collect())->sum(function ($visit) use (&$reviewVisit) {
                    if ($visit->installmentPlan && (int) $visit->installmentPlan->patient_id !== (int) $visit->patient_id) $reviewVisit ??= $visit;
                    $balance = $visit->installmentPlan
                        ? $this->finance->ordinaryBalanceOnFinancedVisit($visit, $visit->installmentPlan)
                        : $this->finance->visitBalance($visit);
                    if ($balance === null) $reviewVisit ??= $visit;
                    return $balance ?? 0;
                });
                $patientPlans = $plans->get($patient->id) ?? collect();
                $financed = $patientPlans->sum(fn ($plan) => $this->finance->planBalance($plan));
                $unknownPlan = $patientPlans->first(fn ($plan) => $plan->hasUnknownTotal());
                $unreceipted = $patientPlans->first(fn ($plan) => (float) $plan->downpayment > 0 && !$this->finance->downpaymentPayment($plan));
                $assumedDownpayments += $patientPlans->filter(fn ($plan) => (float) $plan->downpayment > 0 && !$this->finance->downpaymentPayment($plan))->count();
                $balance = round($ordinary + $financed, 2);
                $name = trim($patient->first_name.' '.$patient->last_name);
                if ($reviewVisit || $unreceipted || $unknownPlan) {
                    $reviewCount++;
                    if (count($reviewItems) < self::LIST_LIMIT) $reviewItems[] = [
                        'patient_id' => $patient->id, 'patient' => $name, 'balance' => $balance,
                        'label' => 'Incomplete balance', 'incomplete' => true,
                        'link_label' => 'Review '.$name.' (#'.$patient->id.') - balance incomplete',
                        'url' => $reviewVisit ? route('staff.visits.show', $reviewVisit) : route('staff.installments.show', $unknownPlan ?: $unreceipted),
                    ];
                    continue;
                }
                if ($balance <= 0) continue;
                $count++;
                $total += $balance;
                $knownItems[] = [
                    'patient_id' => $patient->id, 'patient' => $name, 'balance' => $balance,
                    'label' => 'Recorded balance', 'incomplete' => false,
                    'link_label' => $name.' (#'.$patient->id.') - '.$this->money($balance),
                    'url' => route('staff.patients.show', ['patient' => $patient, 'tab' => 'tab-payments']),
                ];
                usort($knownItems, fn ($a, $b) => $b['balance'] <=> $a['balance']);
                $knownItems = array_slice($knownItems, 0, self::LIST_LIMIT);
            }
        });
        return ['count' => $count, 'total' => round($total, 2), 'incomplete_count' => $reviewCount,
            'affected_count' => $count + $reviewCount, 'known_items' => $knownItems,
            'review_items' => $reviewItems, 'assumed_downpayments' => $assumedDownpayments];
    }

    private function installments(string $today): array
    {
        $count = 0;
        $overdue = 0;
        $dueToday = 0;
        $affected = [];
        $links = [];
        $unscheduled = 0;
        $completedWithBalance = 0;
        $truncated = 0;
        $openDue = 0.0;
        $openPlans = 0;
        $unlinked = InstallmentPlan::whereNull('patient_id')->count();
        InstallmentPlan::with(['payments', 'patient'])->whereHas('patient', fn ($query) => $query->whereNull('deleted_at'))
            ->orderBy('id')->chunkById(100, function ($plans) use ($today, &$count, &$overdue, &$dueToday, &$affected, &$links, &$unscheduled, &$completedWithBalance, &$truncated, &$openDue, &$openPlans) {
                foreach ($plans as $plan) {
                    if ($plan->is_unpriced_contract) {
                        $details = app(OpenMonthlyContractService::class)->details($plan, $today);
                        $openPlans++;
                        $openDue += $details['unpaid_due'];
                        if (count($links) < self::LIST_LIMIT) $links[] = ['label' => 'Open monthly plan #'.$plan->id.' — due ₱'.number_format($details['unpaid_due'], 2),
                            'url' => route('staff.installments.show', $plan).'#installment-schedule'];
                        continue;
                    }
                    $balance = $this->finance->planBalance($plan);
                    if ($balance <= 0) continue;
                    if ($plan->status === InstallmentPlan::STATUS_COMPLETED) { $completedWithBalance++; continue; }
                    if ($plan->is_open_contract || !$plan->start_date || (int) $plan->months < 1) { $unscheduled++; continue; }

                    $downpayment = $this->finance->downpaymentPayment($plan);
                    $shift = $downpayment && (int) $downpayment->month_number === 1
                        && !$plan->payments->contains(fn ($payment) => (int) $payment->month_number === 0) ? 1 : 0;
                    $hasDownpayment = (float) $plan->downpayment > 0 || (bool) $downpayment;
                    $months = min((int) $plan->months, 1200);
                    if ((int) $plan->months > $months) $truncated++;
                    for ($month = 1; $month <= $months; $month++) {
                        $receipt = $plan->payments->first(fn ($payment) => (int) $payment->month_number === $month + $shift
                            && (!$downpayment || !$payment->is($downpayment)));
                        if ($receipt) continue;
                        $due = CarbonImmutable::parse($plan->start_date->toDateString(), config('app.timezone'))
                            ->addMonths(($month - 1) + ($hasDownpayment ? 1 : 0))->toDateString();
                        if ($due > $today) continue;
                        $count++;
                        $affected[$plan->id] = $balance;
                        if ($due < $today) $overdue++; else $dueToday++;
                        if (count($links) < self::LIST_LIMIT) {
                            $links[] = ['label' => 'Plan #'.$plan->id.' month '.$month.' - '.$due.' ('.($due < $today ? 'overdue' : 'due today').')',
                                'url' => route('staff.installments.show', $plan).'#installment-schedule'];
                        }
                    }
                }
            });

        $total = round(array_sum($affected), 2);
        $message = 'As of '.$today.' ('.config('app.timezone').'): '.$count.' scheduled months have no receipt ('.$overdue.' overdue, '.$dueToday.' due today) across '.count($affected).' plans. Remaining balance across those plans: '.$this->money($total).'.'
            .$this->limited($count).' This balance is per plan, not the amount due for each month. The existing plan page marks a month with any receipt as paid, even if the payment was partial; review the plan balance for such cases.';
        if ($unscheduled) $message .= ' '.$unscheduled.' open or undated plan(s) have a balance but no fixed due date and are excluded.';
        if ($openPlans) $message .= ' Separately, '.$openPlans.' open monthly contract(s) have '.$this->money($openDue).' in unpaid monthly obligations due so far. Their monthly dues are separate from any agreed final contract balance; contracts without an agreed total have no determinable final balance.';
        if ($completedWithBalance) $message .= ' '.$completedWithBalance.' completed plan(s) still have a calculated balance and need review.';
        if ($truncated) $message .= ' '.$truncated.' plan(s) exceed the 1,200-month entry limit; only their first 1,200 months were checked.';
        if ($unlinked) $message .= ' '.$unlinked.' plan(s) lack a patient link and were excluded from due counts; review the plan records.';

        return $this->result($message, $count, $total, null, $today, $links);
    }

    private function collections(string $question, string $today): array
    {
        $week = (bool) preg_match('/\bthis week\b/ui', $question);
        $from = $week ? CarbonImmutable::parse($today, config('app.timezone'))->startOfWeek(CarbonImmutable::MONDAY)->toDateString() : $today;
        $to = $week ? CarbonImmutable::parse($today, config('app.timezone'))->endOfWeek(CarbonImmutable::SUNDAY)->toDateString() : $today;
        $ordinary = Payment::whereDate('payment_date', '>=', $from)->whereDate('payment_date', '<=', $to);
        $installments = InstallmentPayment::whereDate('payment_date', '>=', $from)->whereDate('payment_date', '<=', $to);
        $count = (clone $ordinary)->count() + (clone $installments)->count();
        $visibleCount = (clone $ordinary)->whereHas('visit', fn ($query) => $query->whereNull('deleted_at')
            ->whereHas('patient', fn ($patient) => $patient->whereNull('deleted_at')))->count()
            + (clone $installments)->whereHas('plan', fn ($query) => $query->whereNull('deleted_at')
                ->whereHas('patient', fn ($patient) => $patient->whereNull('deleted_at')))->count();
        $archivedCount = $count - $visibleCount;
        $total = $this->finance->collectedBetween($from, $to);
        $rows = (clone $ordinary)->with('visit')->orderByDesc('payment_date')->orderByDesc('id')->limit(self::LIST_LIMIT)->get()
            ->map(fn ($payment) => ['date' => $payment->payment_date?->toDateString(), 'id' => $payment->id,
                'label' => 'Receipt #'.$payment->id.' - '.$this->money((float) $payment->amount),
                'url' => $payment->visit && !$payment->visit->trashed() ? route('staff.payments.show', $payment) : null]);
        $rows = $rows->concat((clone $installments)->with('plan')->orderByDesc('payment_date')->orderByDesc('id')->limit(self::LIST_LIMIT)->get()
            ->map(fn ($payment) => ['date' => $payment->payment_date?->toDateString(), 'id' => $payment->id,
                'label' => 'Installment receipt #'.$payment->id.' - '.$this->money((float) $payment->amount),
                'url' => $payment->plan && !$payment->plan->trashed()
                    ? route('staff.installments.show', $payment->plan).'#installment-payment-'.$payment->id : null]))
            ->sortByDesc(fn ($row) => ($row['date'] ?? '').'-'.str_pad((string) $row['id'], 10, '0', STR_PAD_LEFT))->take(self::LIST_LIMIT);
        $unlinked = $rows->filter(fn ($row) => !$row['url'])->count();
        $links = $rows->filter(fn ($row) => $row['url'])->map(fn ($row) => ['label' => $row['label'], 'url' => $row['url']])->values()->all();
        $message = 'Across the clinic, payments received '.($week ? 'this week' : 'today').' ('.$from.' to '.$to.', '.config('app.timezone').'): '.$count.' receipts totaling '.$this->money($total).'.'
            .$this->limited($count).' Amounts follow the recorded payment date and the existing collections calculation.';
        if ($archivedCount) $message .= ' '.$archivedCount.' receipt(s) are included in the collection total but hidden from the staff Payments ledger because their source or patient is archived; review historical records before reconciling.';
        if ($unlinked) $message .= ' '.$unlinked.' listed receipt(s) have archived source records and cannot be opened from here.';
        return $this->result($message, $count, $total, $from, $to, $links);
    }

    private function visits(?string $from, ?string $to): array
    {
        // Match the staff Visits > All Visits list, including payment-linked visit rows.
        $query = Visit::whereDate('visit_date', '>=', $from)->whereDate('visit_date', '<=', $to);
        $count = (clone $query)->count();
        $links = (clone $query)->orderByDesc('visit_date')->orderByDesc('id')->limit(self::LIST_LIMIT)->get()
            ->map(fn ($visit) => ['label' => 'Visit #'.$visit->id.' - '.$visit->visit_date?->toDateString(),
                'url' => route('staff.visits.show', $visit)])->all();
        return $this->result('Visits from '.$from.' to '.$to.' ('.config('app.timezone').'): '.$count.' visit records.'
            .$this->limited($count).' This matches All Visits, including payment-linked visit rows; the dashboard’s “Visits Recorded” metric uses a narrower treatment rule. Soft-deleted visits are excluded.',
            $count, null, $from, $to, $links);
    }

    private function limited(int $count): string
    {
        return $count > self::LIST_LIMIT ? ' Showing the first '.self::LIST_LIMIT.' supporting records.' : '';
    }

    private function result(string $message, int $count, ?float $total, ?string $from, ?string $to, array $links): array
    {
        return ['message' => $message, 'scope' => 'clinic', 'record_count' => $count,
            'total' => $total === null ? null : round($total, 2),
            'date_range' => ['from' => $from, 'to' => $to, 'timezone' => config('app.timezone')], 'links' => $links];
    }

    private function money(float $amount): string
    {
        return '₱'.number_format($amount, 2);
    }
}
