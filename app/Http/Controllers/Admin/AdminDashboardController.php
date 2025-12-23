<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\InstallmentPlan;
use App\Models\InstallmentPayment;
use App\Models\VisitProcedure;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $start = Carbon::now()->startOfMonth()->toDateString();
        $end   = Carbon::now()->endOfMonth()->toDateString();
        $today = Carbon::today()->toDateString();

        // Cards
        $appointmentsThisMonth = Appointment::whereBetween('appointment_date', [$start, $end])->count();

        $newPatientsThisMonth = Patient::whereBetween(DB::raw('DATE(created_at)'), [$start, $end])->count();

        $cashIncomeThisMonth = Payment::whereBetween('payment_date', [$start, $end])->sum('amount');

        $installmentIncomeThisMonth = InstallmentPayment::whereBetween('payment_date', [$start, $end])->sum('amount');

        $downpaymentsThisMonth = InstallmentPlan::whereBetween('start_date', [$start, $end])->sum('downpayment');

        $totalIncomeThisMonth = (float) $cashIncomeThisMonth
            + (float) $installmentIncomeThisMonth
            + (float) $downpaymentsThisMonth;

        // ✅ this counts performed procedures (visit_procedures rows this month)
        $proceduresThisMonth = VisitProcedure::whereHas('visit', function ($q) use ($start, $end) {
            $q->whereBetween('visit_date', [$start, $end]);
        })->count();

        // ✅ this counts ALL services (what you expected to be 17)
        $servicesTotal = Service::count();

        // Charts
        $patientsByAge = $this->patientsByAgeBuckets();

        // ✅ include all services even if 0 used this month
        $proceduresByService = Service::query()
            ->leftJoin('visit_procedures', 'visit_procedures.service_id', '=', 'services.id')
            ->leftJoin('visits', function ($join) use ($start, $end) {
                $join->on('visits.id', '=', 'visit_procedures.visit_id')
                    ->whereBetween('visits.visit_date', [$start, $end]);
            })
            ->groupBy('services.id', 'services.name', 'services.created_at')
            ->orderByDesc(DB::raw('COUNT(visit_procedures.id)'))
            ->orderByDesc('services.created_at')
            ->limit(20)
            ->get([
                'services.name as name',
                DB::raw('COUNT(visit_procedures.id) as total'),
            ]);

        // Nearest appointments table
        $nearestAppointments = Appointment::with(['patient', 'service'])
            ->whereDate('appointment_date', '>=', $today)
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->limit(10)
            ->get();

        return view('admin.dashboard', [
            'rangeLabel'            => Carbon::now()->format('F Y'),

            'appointmentsThisMonth' => $appointmentsThisMonth,
            'newPatientsThisMonth'  => $newPatientsThisMonth,
            'totalIncomeThisMonth'  => $totalIncomeThisMonth,

            // keep this for real procedure count
            'proceduresThisMonth'   => $proceduresThisMonth,

            // ✅ add this for total services count
            'servicesTotal'         => $servicesTotal,

            'patientsByAge'         => $patientsByAge,
            'proceduresByService'   => $proceduresByService,
            'nearestAppointments'   => $nearestAppointments,
        ]);
    }

    private function patientsByAgeBuckets(): array
    {
        $buckets = [
            '0-12'  => 0,
            '13-19' => 0,
            '20-35' => 0,
            '36-50' => 0,
            '51+'   => 0,
        ];

        $patients = Patient::select('birthdate')->get();

        foreach ($patients as $p) {
            if (!$p->birthdate) continue;

            $age = Carbon::parse($p->birthdate)->age;

            if ($age <= 12) $buckets['0-12']++;
            elseif ($age <= 19) $buckets['13-19']++;
            elseif ($age <= 35) $buckets['20-35']++;
            elseif ($age <= 50) $buckets['36-50']++;
            else $buckets['51+']++;
        }

        return $buckets;
    }
}
