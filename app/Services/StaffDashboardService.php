<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\InstallmentPlan;
use App\Models\Visit;
use Carbon\CarbonImmutable;

class StaffDashboardService
{
    public function __construct(private readonly FinancialService $finance)
    {
    }

    public function data(?CarbonImmutable $clock = null): array
    {
        $timezone = config('app.timezone');
        $now = ($clock ?: CarbonImmutable::now($timezone))->setTimezone($timezone);
        $today = $now->toDateString();
        $tomorrow = $now->addDay()->toDateString();
        $weekEnd = $now->addDays(7)->toDateString();

        $todayAppointments = Appointment::query()
            ->dashboardActive()
            ->whereDate('appointment_date', $today)
            ->with(['patient:id,first_name,last_name', 'service:id,name', 'doctor:id,name'])
            ->orderByRaw('CASE WHEN appointment_time IS NULL THEN 1 ELSE 0 END')
            ->orderBy('appointment_time')
            ->orderBy('id')
            ->get();

        $upcomingAppointments = Appointment::query()
            ->upcomingActive()
            ->whereDate('appointment_date', '>=', $tomorrow)
            ->whereDate('appointment_date', '<=', $weekEnd)
            ->with(['patient:id,first_name,last_name', 'service:id,name', 'doctor:id,name'])
            ->orderBy('appointment_date')
            ->orderByRaw('CASE WHEN appointment_time IS NULL THEN 1 ELSE 0 END')
            ->orderBy('appointment_time')
            ->orderBy('id')
            ->limit(5)
            ->get();

        $pendingQuery = Appointment::query()->whereIn('status', Appointment::STATUS_PENDING);
        $pendingCount = (clone $pendingQuery)->count();
        $oldestRequest = (clone $pendingQuery)
            ->with(['patient:id,first_name,last_name', 'service:id,name', 'doctor:id,name'])
            ->orderBy('appointment_date')
            ->orderByRaw('CASE WHEN appointment_time IS NULL THEN 1 ELSE 0 END')
            ->orderBy('appointment_time')
            ->orderBy('created_at')
            ->first();

        $unreadCount = ContactMessage::query()->whereNull('read_at')->count();
        $latestUnread = ContactMessage::query()
            ->whereNull('read_at')
            ->oldest()
            ->first(['id', 'name', 'email', 'message', 'created_at']);

        [$balanceCount, $balanceItems] = $this->outstandingBalances();
        $attentionCount = $pendingCount + $balanceCount + $unreadCount;

        return [
            'now' => $now,
            'today' => $today,
            'greeting' => $now->hour < 12 ? 'Good morning' : ($now->hour < 18 ? 'Good afternoon' : 'Good evening'),
            'todayAppointments' => $todayAppointments,
            'todayVisitsCount' => Visit::query()
                ->whereDate('visit_date', $today)
                ->where(function ($query) {
                    $query->where('price', '>', 0)
                        ->orWhereHas('procedures', fn ($procedures) => $procedures->where('price', '>', 0))
                        ->orWhereDoesntHave('installmentPayments');
                })
                ->count(),
            'collectedToday' => $this->finance->collectedOn($today),
            'pendingCount' => $pendingCount,
            'oldestRequest' => $oldestRequest,
            'unreadCount' => $unreadCount,
            'latestUnread' => $latestUnread,
            'balanceCount' => $balanceCount,
            'balanceItems' => $balanceItems,
            'attentionCount' => $attentionCount,
            'attentionHelper' => $this->attentionHelper($pendingCount, $balanceCount, $unreadCount),
            'upcomingAppointments' => $upcomingAppointments,
        ];
    }

    private function outstandingBalances(): array
    {
        $procedureTotals = \DB::table('visit_procedures')
            ->selectRaw('visit_id, SUM(COALESCE(price, 0)) AS procedure_total')
            ->groupBy('visit_id');
        $paymentTotals = \DB::table('payments')
            ->selectRaw('visit_id, SUM(COALESCE(amount, 0)) AS payment_total')
            ->whereNull('deleted_at')
            ->groupBy('visit_id');

        $ordinary = Visit::query()
            ->select('visits.*')
            ->selectRaw('COALESCE(visits.price, COALESCE(vp.procedure_total, 0)) - COALESCE(pt.payment_total, 0) AS dashboard_balance')
            ->leftJoinSub($procedureTotals, 'vp', 'vp.visit_id', '=', 'visits.id')
            ->leftJoinSub($paymentTotals, 'pt', 'pt.visit_id', '=', 'visits.id')
            ->whereDoesntHave('installmentPlan')
            ->whereRaw('COALESCE(visits.price, COALESCE(vp.procedure_total, 0)) - COALESCE(pt.payment_total, 0) > 0.009');

        $ordinaryCount = (clone $ordinary)->count('visits.id');
        $ordinaryItems = (clone $ordinary)
            ->with('patient:id,first_name,last_name')
            ->orderByDesc('visit_date')
            ->limit(3)
            ->get()
            ->map(fn (Visit $visit) => [
                'patient_id' => $visit->patient_id,
                'patient' => trim(($visit->patient?->first_name ?? '') . ' ' . ($visit->patient?->last_name ?? '')) ?: 'Patient',
                'label' => 'Visit #' . $visit->id,
                'balance' => (float) $visit->dashboard_balance,
                'target_type' => 'visit',
                'target_id' => $visit->id,
            ]);

        $plans = InstallmentPlan::query()
            ->where('status', '!=', InstallmentPlan::STATUS_COMPLETED)
            ->where('balance', '>', 0.009);
        $planCount = (clone $plans)->count();
        $planItems = (clone $plans)
            ->with(['patient:id,first_name,last_name', 'service:id,name', 'payments'])
            ->orderByDesc('start_date')
            ->limit(3)
            ->get()
            ->map(fn (InstallmentPlan $plan) => [
                'patient_id' => $plan->patient_id,
                'patient' => trim(($plan->patient?->first_name ?? '') . ' ' . ($plan->patient?->last_name ?? '')) ?: 'Patient',
                'label' => $plan->service?->name ?: 'Installment plan #' . $plan->id,
                'balance' => $this->finance->planBalance($plan),
                'target_type' => 'plan',
                'target_id' => $plan->id,
            ])
            ->filter(fn (array $item) => $item['balance'] > 0);

        return [$ordinaryCount + $planCount, $ordinaryItems->concat($planItems)->sortByDesc('balance')->take(3)->values()];
    }

    private function attentionHelper(int $requests, int $balances, int $messages): string
    {
        return collect([
            $requests ? $requests . ' ' . str('request')->plural($requests) : null,
            $balances ? $balances . ' ' . str('balance')->plural($balances) : null,
            $messages ? $messages . ' ' . str('message')->plural($messages) : null,
        ])->filter()->join(' · ') ?: 'Nothing waiting';
    }
}
