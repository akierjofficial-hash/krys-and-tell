<?php

namespace App\Services;

use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Visit;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Http;

class StaffRecordAssistant
{
    public function __construct(private FinancialService $finance) {}

    public function patients(string $search): array
    {
        $search = trim($search);
        if (mb_strlen($search) < 2) return [];

        $terms = array_slice(preg_split('/\s+/', $search), 0, 4);
        return Patient::query()->where(function ($query) use ($terms) {
            foreach ($terms as $term) {
                $query->where(function ($part) use ($term) {
                    $part->whereLike('first_name', '%'.addcslashes($term, '%_\\').'%')
                        ->orWhereLike('last_name', '%'.addcslashes($term, '%_\\').'%')
                        ->orWhereLike('middle_name', '%'.addcslashes($term, '%_\\').'%');
                });
            }
        })->orderBy('last_name')->orderBy('first_name')->limit(10)
            ->get(['id', 'first_name', 'middle_name', 'last_name', 'birthdate'])
            ->map(fn ($patient) => [
                'id' => $patient->id,
                'name' => implode(' ', array_filter([$patient->first_name, $patient->middle_name, $patient->last_name], fn ($part) => filled($part))),
                'birthdate' => $patient->birthdate ? Carbon::parse($patient->birthdate)->toDateString() : null,
            ])->all();
    }

    public function answer(Patient $patient, string $question): array
    {
        $intent = $this->intent($question);
        return match ($intent) {
            'balance' => $this->balance($patient),
            'identity' => $this->identity($patient),
            'visits' => $this->visits($patient),
            'visit_count' => $this->visitCount($patient),
            'payments' => $this->payments($patient),
            'due' => $this->due($patient),
            'provider_limited' => $this->result('The AI provider is rate limited right now. The standard record questions still work without it.', $patient),
            'provider_misconfigured' => $this->result('Ollama is set to a localhost address on Render, which cannot reach the clinic computer. Ask an administrator to use rules mode or configure a server-reachable private Ollama service.', $patient),
            'provider_unavailable' => $this->result('The AI provider is unavailable right now. The standard record questions still work without it.', $patient),
            default => $this->result('I cannot verify that question from the supported records. I can show the selected patient’s identifiers, calculated balance, visit records, recorded receipts, and installment schedule. Please use a suggested question or open the patient profile.', $patient),
        };
    }

    private function intent(string $question): string
    {
        $text = mb_strtolower($question);
        if (preg_match('/\b(who is|identify|patient id|date of birth|birthdate)\b/u', $text)) return 'identity';
        if (preg_match('/\b(installments?|months?)\b.*\b(due|overdue|unpaid|next|pending)\b|\b(due|overdue|unpaid|next|pending)\b.*\b(installments?|months?)\b/u', $text)) return 'due';
        if (preg_match('/\b(balances?|owe|owes|outstanding|remaining|how much left)\b/u', $text)) return 'balance';
        if (preg_match('/\b(amount due|how much is due)\b/u', $text)) return 'balance';
        if (preg_match('/\b(due|overdue|unpaid month|next installment|remaining installment|pending installment)\b/u', $text)) return 'due';
        if (preg_match('/\b(payments?|pay|paid|receipts?|transactions?|downpayments?)\b/u', $text)) return 'payments';
        if (preg_match('/\b(how many|count|number of)\b.*\bvisits?\b|\bvisits?\b.*\b(how many|count)\b/u', $text)) return 'visit_count';
        if (preg_match('/\b(visits?|treatments?|procedures?|seen|dentist)\b/u', $text)) return 'visits';

        if (config('staff_assistant.provider') !== 'ollama') return 'unknown';
        // Do not forward arbitrary staff text: it may contain a name, diagnosis, or payment details.
        // Only these fixed, generic aliases can reach a configured provider.
        $safeQuestion = match (trim($text, " \t\n\r\0\x0B?.!")) {
            'tell me about prior encounters', 'show previous encounters', 'summarize prior encounters' => 'Tell me about prior encounters',
            'show previous receipts', 'summarize previous receipts' => 'Show previous receipts',
            default => null,
        };
        if ($safeQuestion === null) return 'unknown';
        $host = strtolower((string) parse_url((string) config('staff_assistant.ollama_url'), PHP_URL_HOST));
        if (config('staff_assistant.on_render') && in_array($host, ['localhost', '127.0.0.1', '::1'], true)) return 'provider_misconfigured';
        try {
            // This is a fixed generic alias, never raw staff text or a patient record.
            $response = Http::timeout(4)->post(rtrim(config('staff_assistant.ollama_url'), '/').'/api/generate', [
                'model' => config('staff_assistant.ollama_model'),
                'stream' => false,
                'prompt' => 'Classify this clinic staff question. Reply with exactly one word: balance, visits, payments, due, or unknown. Do not answer the question. Question: '.$safeQuestion,
            ]);
            if ($response->status() === 429) return 'provider_limited';
            if (!$response->successful()) return 'provider_unavailable';
            $label = trim(mb_strtolower((string) $response->json('response')));
            return in_array($label, ['balance', 'visits', 'payments', 'due'], true) ? $label : 'unknown';
        } catch (\Throwable) {
            return 'provider_unavailable';
        }
    }

    private function identity(Patient $patient): array
    {
        $name = implode(' ', array_filter([$patient->first_name, $patient->middle_name, $patient->last_name], fn ($part) => filled($part)));
        $birthdate = $patient->birthdate ? Carbon::parse($patient->birthdate)->toDateString() : 'not recorded';
        return $this->result('Selected patient: '.$name.'; patient ID #'.$patient->id.'; birthdate: '.$birthdate.'. Confirm identity against the patient profile before using clinical or payment records.', $patient);
    }

    private function balance(Patient $patient): array
    {
        $visits = Visit::with(['procedures', 'payments', 'installmentPlan'])->where('patient_id', $patient->id)->get();
        $plans = InstallmentPlan::with('payments')->where('patient_id', $patient->id)->get();
        if ($visits->isEmpty() && $plans->isEmpty()) return $this->result('No visits or installment plans were found, so I cannot report a balance.', $patient, [], 'tab-payments');

        $unresolved = [];
        $mislinkedPlans = [];
        $ordinary = $visits->sum(function ($visit) use (&$unresolved) {
            $amount = $visit->installmentPlan
                ? $this->finance->ordinaryBalanceOnFinancedVisit($visit, $visit->installmentPlan)
                : $this->finance->visitBalance($visit);
            if ($amount === null) $unresolved[] = $visit;
            return $amount ?? 0;
        });
        foreach ($visits as $visit) {
            if ($visit->installmentPlan && (int) $visit->installmentPlan->patient_id !== (int) $patient->id) $mislinkedPlans[] = $visit;
        }
        $installments = $plans->sum(fn ($plan) => $this->finance->planBalance($plan));
        $unknownPlans = $plans->filter(fn ($plan) => $plan->hasUnknownTotal());
        $missingDownpayments = $plans->filter(fn ($plan) => (float) $plan->downpayment > 0 && !$this->finance->downpaymentPayment($plan));
        $links = [];
        foreach ($visits->filter(function ($visit) {
            return ($visit->installmentPlan
                ? $this->finance->ordinaryBalanceOnFinancedVisit($visit, $visit->installmentPlan)
                : $this->finance->visitBalance($visit)) > 0;
        })->take(5) as $visit) {
            $links[] = ['label' => 'Visit #'.$visit->id, 'url' => route('staff.visits.show', $visit)];
        }
        foreach ($plans->filter(fn ($plan) => $this->finance->planBalance($plan) > 0)->take(5) as $plan) {
            $links[] = ['label' => 'Plan #'.$plan->id, 'url' => route('staff.installments.show', $plan)];
        }
        $message = (($unresolved || $mislinkedPlans || $missingDownpayments->isNotEmpty() || $unknownPlans->isNotEmpty()) ? 'Known recorded balance (incomplete)' : 'Remaining balance').': '.$this->money($ordinary + $installments).'. Ordinary visits: '.$this->money($ordinary).'; installment plans: '.$this->money($installments).'.';
        if ($unknownPlans->isNotEmpty()) {
            $message .= ' Final contract balance: Not determinable — no total agreed for '. $unknownPlans->count().' open monthly contract(s).';
            foreach ($unknownPlans->take(5) as $plan) $links[] = ['label' => 'Open monthly plan #'.$plan->id, 'url' => route('staff.installments.show', $plan)];
        }
        if ($unresolved) {
            $message .= ' A complete balance is unavailable: the ordinary charges on mixed visit(s) '.collect($unresolved)->pluck('id')->map(fn ($id) => '#'.$id)->implode(', ').' cannot be allocated safely.';
            foreach (array_slice($unresolved, 0, 5) as $visit) $links[] = ['label' => 'Review mixed visit #'.$visit->id, 'url' => route('staff.visits.show', $visit)];
        }
        if ($mislinkedPlans) {
            $message .= ' A complete balance is unavailable: '.count($mislinkedPlans).' visit-linked plan(s) do not identify this patient and were not included in the plan total.';
            foreach (array_slice($mislinkedPlans, 0, 5) as $visit) $links[] = ['label' => 'Review plan link on visit #'.$visit->id, 'url' => route('staff.visits.show', $visit)];
        }
        if ($missingDownpayments->isNotEmpty()) {
            $message .= ' '. $missingDownpayments->count().' plan(s) count an entered downpayment without a receipt; this is the existing balance rule, not proof that cash was received.';
            foreach ($missingDownpayments->take(5) as $plan) $links[] = ['label' => 'Review downpayment on plan #'.$plan->id, 'url' => route('staff.installments.show', $plan)];
        }
        return $this->result($message, $patient, $links, 'tab-payments');
    }

    private function visits(Patient $patient): array
    {
        $visits = Visit::with(['procedures.service', 'installmentPayments'])->where('patient_id', $patient->id)
            ->orderByDesc('visit_date')->orderByDesc('id')->limit(5)->get();
        if ($visits->isEmpty()) return $this->result('No visits were found for this patient.', $patient, [], 'tab-visits');
        $lines = $visits->map(function ($visit) {
            $names = $visit->procedures->map(fn ($row) => $row->service?->name)->filter()->unique()->implode(', ');
            $description = $names ?: ($visit->installmentPayments->isNotEmpty() && (float) $visit->price <= 0
                ? 'Payment-linked visit row; no treatment recorded' : 'No treatment recorded');
            return ($visit->visit_date?->format('M j, Y') ?? 'Date not recorded').' — '.$description.'.';
        });
        $links = $visits->map(fn ($visit) => ['label' => 'Visit #'.$visit->id, 'url' => route('staff.visits.show', $visit)])->all();
        return $this->result("Recent visit records (newest first):\n".$lines->implode("\n"), $patient, $links, 'tab-visits');
    }

    private function visitCount(Patient $patient): array
    {
        $count = Visit::where('patient_id', $patient->id)->count();
        return $this->result('This patient has '.$count.' recorded visit '.($count === 1 ? 'row' : 'rows').', including any payment-linked visit rows. Soft-deleted rows are excluded.', $patient, [], 'tab-visits');
    }

    private function payments(Patient $patient): array
    {
        $unlinkedPlans = $this->unlinkedPlansCount($patient);
        $ordinary = Payment::with('visit')->whereHas('visit', fn ($query) => $query->where('patient_id', $patient->id)->whereNull('deleted_at'))
            ->orderByDesc('payment_date')->limit(10)->get()->map(fn ($payment) => [
                'date' => $payment->payment_date?->toDateString(), 'id' => $payment->id,
                'text' => 'Ordinary payment '.$this->money((float) $payment->amount).' ('.$payment->method.')',
                'link' => ['label' => 'Receipt #'.$payment->id, 'url' => route('staff.payments.show', $payment)],
            ]);
        $installments = InstallmentPayment::with('plan')->whereHas('plan', fn ($query) => $query->where('patient_id', $patient->id)->whereNull('deleted_at'))
            ->orderByDesc('payment_date')->limit(10)->get()->map(fn ($payment) => [
                'date' => $payment->payment_date?->toDateString(), 'id' => $payment->id,
                'text' => 'Installment payment '.$this->money((float) $payment->amount).' ('.$payment->method.')',
                'link' => ['label' => 'Installment receipt #'.$payment->id, 'url' => route('staff.installments.show', $payment->installment_plan_id).'#installment-payment-'.$payment->id],
            ]);
        $rows = $ordinary->concat($installments)->sortByDesc(fn ($row) => ($row['date'] ?? '').'-'.str_pad((string) $row['id'], 10, '0', STR_PAD_LEFT))->take(10);
        if ($rows->isEmpty()) return $this->result('No payment receipts were found on patient-linked records.'.($unlinkedPlans ? ' '.$unlinkedPlans.' visit-linked plan(s) lack a patient link; their receipts were not attributed here.' : ''), $patient, [], 'tab-payments');
        return $this->result("Latest payment receipts:\n".$rows->map(fn ($row) => ($row['date'] ?? 'Date unknown').' — '.$row['text'])->implode("\n")
            .($unlinkedPlans ? "\n".$unlinkedPlans.' visit-linked plan(s) lack a patient link; their receipts were not attributed here.' : ''),
            $patient, $rows->pluck('link')->all(), 'tab-payments');
    }

    private function due(Patient $patient): array
    {
        $today = CarbonImmutable::now(config('app.timezone'))->toDateString();
        $plans = InstallmentPlan::with('payments')->where('patient_id', $patient->id)->get();
        $unlinkedPlans = $this->unlinkedPlansCount($patient);
        if ($plans->isEmpty()) return $this->result('No patient-linked installment plans were found.'.($unlinkedPlans ? ' '.$unlinkedPlans.' visit-linked plan(s) lack a patient link; their due status is unavailable here.' : ''), $patient, [], 'tab-payments');
        $lines = [];
        $links = [];
        foreach ($plans as $plan) {
            if ($plan->is_unpriced_contract) {
                $details = app(\App\Services\OpenMonthlyContractService::class)->details($plan);
                $final = $plan->hasUnknownTotal() ? 'Not determinable — no total agreed' : $this->money($this->finance->planBalance($plan));
                $lines[] = 'Plan #'.$plan->id.': monthly '.$this->money($details['monthly_amount']).'; unpaid obligations due so far '.$this->money($details['unpaid_due']).'; next due '.($details['next_due_date'] ?: 'none (closed)').'. Final balance: '.$final.'.';
                $links[] = ['label' => 'Plan #'.$plan->id.' monthly schedule', 'url' => route('staff.installments.show', $plan).'#installment-schedule'];
                continue;
            }
            $balance = $this->finance->planBalance($plan);
            if ($balance <= 0) continue;
            $links[] = ['label' => 'Plan #'.$plan->id.' schedule', 'url' => route('staff.installments.show', $plan).'#installment-schedule'];
            if ($plan->status === InstallmentPlan::STATUS_COMPLETED) {
                $lines[] = 'Plan #'.$plan->id.': '.$this->money($balance).' remains in the calculation, but this plan is marked completed. Review the plan record.';
                continue;
            }
            if ($plan->is_open_contract || !$plan->start_date || (int) $plan->months < 1) {
                $lines[] = 'Plan #'.$plan->id.': '.$this->money($balance).' remains; no fixed monthly due schedule is available.';
                continue;
            }
            $downpayment = $this->finance->downpaymentPayment($plan);
            $shift = $downpayment && (int) $downpayment->month_number === 1
                && !$plan->payments->contains(fn ($payment) => (int) $payment->month_number === 0) ? 1 : 0;
            $hasDownpayment = (float) $plan->downpayment > 0 || (bool) $downpayment;
            $unpaid = [];
            $upcoming = 0;
            for ($month = 1; $month <= min((int) $plan->months, 1200); $month++) {
                if ($plan->payments->contains(fn ($payment) => (int) $payment->month_number === $month + $shift && (!$downpayment || !$payment->is($downpayment)))) continue;
                $date = Carbon::parse($plan->start_date)->addMonths(($month - 1) + ($hasDownpayment ? 1 : 0));
                if ($date->toDateString() > $today) { $upcoming++; continue; }
                $unpaid[] = 'month '.$month.' ('.$date->format('M j, Y').', '.($date->toDateString() < $today ? 'overdue' : 'due today').')';
            }
            $lines[] = 'Plan #'.$plan->id.': '.$this->money($balance).' remains. '.($unpaid ? 'No receipt recorded for '.implode(', ', array_slice($unpaid, 0, 6)).(count($unpaid) > 6 ? ' and '.(count($unpaid) - 6).' more' : '').'.' : 'No scheduled month is currently due without a receipt.').($upcoming ? ' '.$upcoming.' future month(s) have no receipt yet.' : '');
        }
        return $this->result(($lines ? implode("\n", $lines) : 'No installment balance remains on the recorded plans.').' Due dates use '.config('app.timezone').' as of '.$today.'. A month with any receipt is treated as paid by the existing schedule; check the plan balance for partial receipts.'
            .($unlinkedPlans ? ' '.$unlinkedPlans.' visit-linked plan(s) lack a patient link and were excluded.' : ''), $patient, $links, 'tab-payments');
    }

    private function unlinkedPlansCount(Patient $patient): int
    {
        return InstallmentPlan::whereNull('patient_id')->whereHas('visit', fn ($query) => $query->where('patient_id', $patient->id)->whereNull('deleted_at'))->count();
    }

    private function result(string $message, Patient $patient, array $links = [], ?string $tab = null): array
    {
        return ['message' => $message, 'links' => array_merge([
            ['label' => 'Patient #'.$patient->id, 'url' => route('staff.patients.show', array_filter(['patient' => $patient->id, 'tab' => $tab]))],
        ], $links)];
    }

    private function money(float $amount): string
    {
        return '₱'.number_format($amount, 2);
    }
}
