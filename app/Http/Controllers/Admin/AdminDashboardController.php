<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\InstallmentPlan;
use App\Models\InstallmentPayment;
use App\Models\VisitProcedure;
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

        // If your installment plan downpayment is considered received upon creation/start_date:
        $downpaymentsThisMonth = InstallmentPlan::whereBetween('start_date', [$start, $end])->sum('downpayment');

        $totalIncomeThisMonth = (float)$cashIncomeThisMonth + (float)$installmentIncomeThisMonth + (float)$downpaymentsThisMonth;

        $proceduresThisMonth = VisitProcedure::whereHas('visit', function ($q) use ($start, $end) {
            $q->whereBetween('visit_date', [$start, $end]);
        })->count();

        // Charts
        $patientsByAge = $this->patientsByAgeBuckets();

        $proceduresByService = VisitProcedure::select('services.name as name', DB::raw('COUNT(*) as total'))
            ->join('services', 'services.id', '=', 'visit_procedures.service_id')
            ->join('visits', 'visits.id', '=', 'visit_procedures.visit_id')
            ->whereBetween('visits.visit_date', [$start, $end])
            ->groupBy('services.name')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

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
            'proceduresThisMonth'   => $proceduresThisMonth,

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
