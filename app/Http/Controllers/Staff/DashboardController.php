<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\Visit;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Models\InstallmentPlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $now = Carbon::now();
        $today = $now->toDateString();

        $hour = $now->hour;
        $greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');

        $user = Auth::user();

        $totalPatients = Patient::count();
        $todaysVisits = Visit::whereDate('visit_date', $today)->count();
        $todaysPayments = Payment::whereDate('payment_date', $today)->sum('amount');
        $services = Service::count();

        $todaysAppointments = Appointment::whereDate('appointment_date', $today)
            ->with(['patient', 'service'])
            ->orderBy('appointment_time')
            ->get();

        $upcomingAppointments = Appointment::where('status', 'upcoming')
            ->whereDate('appointment_date', '>=', $today)
            ->orderBy('appointment_date', 'asc')
            ->get();

        $overdueInstallments = collect();

        try {
            $plans = InstallmentPlan::with(['payments', 'visit.patient', 'service'])->get();
            $todayCarbon = Carbon::parse($today);

            $items = [];

            foreach ($plans as $plan) {
                $start = Carbon::parse($plan->start_date);
                $months = (int) ($plan->months ?? 0);
                if ($months <= 0) continue;

                $down = (float) ($plan->downpayment ?? 0);
                $totalCost = (float) ($plan->total_cost ?? 0);

                $monthlyExpected = ($months > 1)
                    ? max(0, ($totalCost - $down) / max(1, ($months - 1)))
                    : $totalCost;

                for ($m = 1; $m <= $months; $m++) {
                    $dueDate = $start->copy()->addMonths($m - 1);

                    if (!$dueDate->lt($todayCarbon)) {
                        continue;
                    }

                    $paid = 0.0;

                    if ($m === 1) {
                        $paid = (float) ($plan->downpayment ?? 0);
                    } else {
                        $p = $plan->payments->firstWhere('month_number', $m);
                        $paid = (float) ($p->amount ?? 0);
                    }

                    if ($paid > 0) continue;

                    $patient = $plan->visit?->patient;
                    $patientName = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
                    if ($patientName === '') $patientName = 'Patient';

                    $serviceName = $plan->service?->name ?? 'Installment Plan';

                    $items[] = [
                        'patient' => $patientName,
                        'service' => $serviceName,
                        'due_date' => $dueDate->format('M d, Y'),
                        'days_overdue' => $dueDate->diffInDays($todayCarbon),
                        'amount' => ($m === 1) ? $down : $monthlyExpected,
                        'url' => route('staff.installments.show', ['plan' => $plan->id]),
                    ];

                    break;
                }
            }

            $overdueInstallments = collect($items)
                ->sortByDesc('days_overdue')
                ->values()
                ->take(5);

        } catch (\Throwable $e) {
            $overdueInstallments = collect();
        }

        $displayDate = $now->format('l, M d, Y');
        $displayTime = $now->format('h:i A');

        return view('staff.dashboard.index', compact(
            'totalPatients',
            'todaysVisits',
            'todaysPayments',
            'todaysAppointments',
            'upcomingAppointments',
            'services',
            'greeting',
            'user',
            'displayDate',
            'displayTime',
            'overdueInstallments'
        ));
    }

    public function calendarEvents(Request $request)
    {
        $start = Carbon::parse($request->get('start'));
        $end   = Carbon::parse($request->get('end'));

        $events = [];

        $appointments = Appointment::with(['patient', 'service'])
            ->whereBetween('appointment_date', [$start->toDateString(), $end->toDateString()])
            ->get();

        foreach ($appointments as $a) {
            $date = Carbon::parse($a->appointment_date);
            $time = $a->appointment_time ?? '09:00:00';

            $startDt = Carbon::parse($date->format('Y-m-d') . ' ' . $time);
            $endDt = $startDt->copy()->addMinutes(60);

            $patient = trim(($a->patient?->first_name ?? '') . ' ' . ($a->patient?->last_name ?? ''));
            $service = $a->service?->name ?? 'Appointment';

            $status = strtolower((string)($a->status ?? 'confirmed'));

            $color = match (true) {
                str_contains($status, 'cancel') => '#ef4444',
                str_contains($status, 'pend')   => '#f59e0b',
                str_contains($status, 'done') || str_contains($status, 'complete') => '#22c55e',
                default => '#3b82f6',
            };

            $events[] = [
                'title' => ($patient ?: 'Patient') . ' — ' . $service,
                'start' => $startDt->toIso8601String(),
                'end'   => $endDt->toIso8601String(),
                'backgroundColor' => $color,
                'borderColor'     => $color,
                'textColor'       => '#ffffff',
                'extendedProps' => [
                    'type' => 'appointment',
                    'url' => route('staff.appointments.show', ['appointment' => $a->id]),
                ],
            ];
        }

        try {
            $plans = InstallmentPlan::with(['payments', 'visit.patient'])->get();
            $today = Carbon::today();

            foreach ($plans as $plan) {
                $startDate = Carbon::parse($plan->start_date);
                $months = (int)($plan->months ?? 0);
                if ($months <= 0) continue;

                for ($m = 1; $m <= $months; $m++) {
                    $due = $startDate->copy()->addMonths($m - 1);
                    if ($due->lt($start) || $due->gt($end)) continue;

                    $paid = 0.0;
                    if ($m === 1) {
                        $paid = (float)($plan->downpayment ?? 0);
                    } else {
                        $p = $plan->payments->firstWhere('month_number', $m);
                        $paid = (float)($p->amount ?? 0);
                    }

                    if ($paid > 0) continue;

                    $patient = $plan->visit?->patient;
                    $name = trim(($patient->first_name ?? '') . ' ' . ($patient->last_name ?? ''));
                    if ($name === '') $name = 'Patient';

                    $isOverdue = $due->lt($today);
                    $color = $isOverdue ? '#ef4444' : '#f97316';

                    $events[] = [
                        'title' => 'Installment Due — ' . $name,
                        'start' => $due->toDateString(),
                        'allDay' => true,
                        'backgroundColor' => $color,
                        'borderColor'     => $color,
                        'textColor'       => '#ffffff',
                        'extendedProps' => [
                            'type' => 'installment',
                            'url'  => route('staff.installments.show', ['plan' => $plan->id]),
                        ],
                    ];
                }
            }
        } catch (\Throwable $e) {
        }

        return response()->json($events);
    }
}
