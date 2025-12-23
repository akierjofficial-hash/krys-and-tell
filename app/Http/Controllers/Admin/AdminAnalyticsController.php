<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        // -----------------------------
        // 1) Date Range (GET filters)
        // -----------------------------
        $range = $request->get('range', '30'); // 7 | 30 | month | custom
        $today = Carbon::today();

        if ($range === '7') {
            $end = $today->copy();
            $start = $today->copy()->subDays(6);
        } elseif ($range === 'month') {
            $start = $today->copy()->startOfMonth();
            $end = $today->copy();
        } elseif ($range === 'custom') {
            $startInput = $request->get('start_date');
            $endInput   = $request->get('end_date');

            $start = $startInput ? Carbon::parse($startInput)->startOfDay() : $today->copy()->subDays(29);
            $end   = $endInput   ? Carbon::parse($endInput)->startOfDay()   : $today->copy();

            if ($start->gt($end)) {
                [$start, $end] = [$end, $start];
            }
        } else {
            // default last 30
            $end = $today->copy();
            $start = $today->copy()->subDays(29);
            $range = '30';
        }

        // Build day labels
        $days = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $days[] = $cursor->toDateString();
            $cursor->addDay();
        }

        $daysCount = count($days);

        // -----------------------------
        // 2) Previous Period Range
        // -----------------------------
        $prevEnd = Carbon::parse($days[0])->subDay(); // day before current start
        $prevStart = $prevEnd->copy()->subDays(max($daysCount - 1, 0));

        // -----------------------------
        // Helpers to build series
        // -----------------------------
        $revenueMap = function (Carbon $s, Carbon $e) {
            $s = $s->toDateString();
            $e = $e->toDateString();

            $cash = DB::table('payments')
                ->selectRaw('payment_date as d, SUM(amount) as total')
                ->whereBetween('payment_date', [$s, $e])
                ->groupBy('payment_date')
                ->pluck('total', 'd');

            $inst = DB::table('installment_payments')
                ->selectRaw('payment_date as d, SUM(amount) as total')
                ->whereBetween('payment_date', [$s, $e])
                ->groupBy('payment_date')
                ->pluck('total', 'd');

            // combine
            $out = [];
            foreach ($cash as $d => $t) $out[$d] = (float)$t;
            foreach ($inst as $d => $t) $out[$d] = (float)($out[$d] ?? 0) + (float)$t;

            return $out; // [Y-m-d => total]
        };

        $appointmentsMap = function (Carbon $s, Carbon $e) {
            return DB::table('appointments')
                ->selectRaw('DATE(appointment_date) as d, COUNT(*) as total')
                ->whereBetween(DB::raw('DATE(appointment_date)'), [$s->toDateString(), $e->toDateString()])
                ->groupBy('d')
                ->pluck('total', 'd'); // [Y-m-d => count]
        };

        $patientsMap = function (Carbon $s, Carbon $e) {
            return DB::table('patients')
                ->selectRaw('DATE(created_at) as d, COUNT(*) as total')
                ->whereBetween(DB::raw('DATE(created_at)'), [$s->toDateString(), $e->toDateString()])
                ->groupBy('d')
                ->pluck('total', 'd'); // [Y-m-d => count]
        };

        $buildSeries = function (array $dayList, $map) {
            $series = [];
            foreach ($dayList as $d) {
                $series[] = (float)($map[$d] ?? 0);
            }
            return $series;
        };

        $percentChange = function (float $current, float $previous) {
            if ($previous == 0.0) {
                if ($current == 0.0) return 0.0;
                return null; // show "—" (avoid infinite %)
            }
            return (($current - $previous) / $previous) * 100.0;
        };

        // -----------------------------
        // 3) Current Period Data
        // -----------------------------
        $revMapNow = $revenueMap($start, $end);
        $apptMapNow = $appointmentsMap($start, $end);
        $patMapNow = $patientsMap($start, $end);

        $revenueSeries = $buildSeries($days, $revMapNow);
        $appointmentsSeries = array_map('intval', $buildSeries($days, $apptMapNow));
        $patientsSeries = array_map('intval', $buildSeries($days, $patMapNow));

        // KPIs
        $kpiRevenue = array_sum($revenueSeries);
        $kpiAppointments = array_sum($appointmentsSeries);
        $kpiNewPatients = array_sum($patientsSeries);

        // Top services (uses visits.visit_date)
        $topServices = DB::table('visit_procedures')
            ->join('visits', 'visits.id', '=', 'visit_procedures.visit_id')
            ->join('services', 'services.id', '=', 'visit_procedures.service_id')
            ->whereBetween('visits.visit_date', [$start->toDateString(), $end->toDateString()])
            ->groupBy('services.id', 'services.name', 'services.color')
            ->orderByDesc(DB::raw('COUNT(*)'))
            ->limit(6)
            ->get([
                'services.id',
                'services.name',
                'services.color',
                DB::raw('COUNT(*) as total'),
            ]);

        $kpiTopService = $topServices->first()->name ?? '—';

        // -----------------------------
        // 4) Previous Period Totals
        // -----------------------------
        // Build previous day list aligned to same length
        $prevDays = [];
        $pc = $prevStart->copy();
        while ($pc->lte($prevEnd)) {
            $prevDays[] = $pc->toDateString();
            $pc->addDay();
        }

        $revMapPrev = $revenueMap($prevStart, $prevEnd);
        $apptMapPrev = $appointmentsMap($prevStart, $prevEnd);
        $patMapPrev = $patientsMap($prevStart, $prevEnd);

        $prevRevenueTotal = array_sum($buildSeries($prevDays, $revMapPrev));
        $prevAppointmentsTotal = array_sum(array_map('intval', $buildSeries($prevDays, $apptMapPrev)));
        $prevPatientsTotal = array_sum(array_map('intval', $buildSeries($prevDays, $patMapPrev)));

        $revenueChangePct = $percentChange((float)$kpiRevenue, (float)$prevRevenueTotal);
        $appointmentsChangePct = $percentChange((float)$kpiAppointments, (float)$prevAppointmentsTotal);
        $patientsChangePct = $percentChange((float)$kpiNewPatients, (float)$prevPatientsTotal);

        // Nice label for UI
        $periodLabel = $start->format('M d, Y') . ' – ' . $end->format('M d, Y');

        return view('admin.analytics.index', [
            'range' => $range,
            'startDate' => $start->toDateString(),
            'endDate' => $end->toDateString(),
            'periodLabel' => $periodLabel,

            'labels' => $days,
            'revenueSeries' => $revenueSeries,
            'appointmentsSeries' => $appointmentsSeries,
            'patientsSeries' => $patientsSeries,
            'topServices' => $topServices,

            'kpiRevenue' => $kpiRevenue,
            'kpiAppointments' => $kpiAppointments,
            'kpiNewPatients' => $kpiNewPatients,
            'kpiTopService' => $kpiTopService,

            'revenueChangePct' => $revenueChangePct,
            'appointmentsChangePct' => $appointmentsChangePct,
            'patientsChangePct' => $patientsChangePct,
        ]);
    }
}
