<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\Visit;
use Carbon\CarbonImmutable;

class StaffDashboardService
{
    public function __construct(private readonly FinancialService $finance, private readonly StaffClinicAssistant $clinicAssistant)
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

        $balances = $this->clinicAssistant->balanceOverview();
        $balanceCount = $balances['affected_count'];
        $balanceItems = collect($balances['review_items'])->concat($balances['known_items'])->take(3);
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
            'balanceKnownTotal' => $balances['total'],
            'balanceIncompleteCount' => $balances['incomplete_count'],
            'balanceKnownCount' => $balances['count'],
            'attentionCount' => $attentionCount,
            'attentionHelper' => $this->attentionHelper($pendingCount, $balanceCount, $unreadCount),
            'upcomingAppointments' => $upcomingAppointments,
        ];
    }

    private function attentionHelper(int $requests, int $balances, int $messages): string
    {
        return collect([
            $requests ? $requests . ' ' . str('request')->plural($requests) : null,
            $balances ? $balances . ' patient ' . str('balance')->plural($balances) . ' to review' : null,
            $messages ? $messages . ' ' . str('message')->plural($messages) : null,
        ])->filter()->join(' · ') ?: 'Nothing waiting';
    }
}
