<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\FinancialService;

class AdminPatientController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $patients = Patient::query()
            ->when($q !== '', function ($query) use ($q) {
                $terms = preg_split('/[\s,]+/u', $q, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $query->where(function ($search) use ($terms) {
                    foreach ($terms as $term) {
                        $search->where(function ($part) use ($term) {
                            $like = "%{$term}%";
                            $part->whereLike('first_name', $like)
                                ->orWhereLike('last_name', $like)
                                ->orWhereLike('middle_name', $like)
                                ->orWhereLike('contact_number', $like)
                                ->orWhereLike('email', $like);
                        });
                    }
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('admin.patients.index', compact('patients', 'q'));
    }

    public function show(Patient $patient, FinancialService $financials)
    {
        $patient->load(['files', 'verifiedUsers']);

        // Appointment History (from staff appointments)
        $appointments = $patient->appointments()
            ->with('service')
            ->orderBy('appointment_date', 'desc')
            ->orderBy('appointment_time', 'desc')
            ->get();

        $now = Carbon::now();

        $upcoming = $appointments->filter(function ($a) use ($now) {
            if (!$a->appointment_date || !$a->appointment_time) return false;
            return Carbon::parse($a->appointment_date.' '.$a->appointment_time)->gte($now);
        })->values();

        $past = $appointments->filter(function ($a) use ($now) {
            if (!$a->appointment_date || !$a->appointment_time) return true;
            return Carbon::parse($a->appointment_date.' '.$a->appointment_time)->lt($now);
        })->values();

        // Treatment = procedures from visits (visit_procedures + services)
        $procedures = $patient->visits()
            ->with(['procedures.service'])
            ->orderBy('visit_date', 'desc')
            ->get()
            ->flatMap(function ($visit) {
                return $visit->procedures->map(function ($vp) use ($visit) {
                    $vp->visit_date = $visit->visit_date;
                    return $vp;
                });
            })
            ->values();

        $ordinaryVisits = $patient->visits()->with(['procedures', 'payments', 'installmentPlan'])->get();
        $plans = \App\Models\InstallmentPlan::with('payments')->where('patient_id', $patient->id)->get();
        $incompleteMixed = 0;
        $ordinaryOutstanding = $ordinaryVisits->sum(function ($visit) use ($financials, &$incompleteMixed) {
            if (!$visit->installmentPlan) return $financials->visitBalance($visit);
            $balance = $financials->ordinaryBalanceOnFinancedVisit($visit, $visit->installmentPlan);
            if ($balance === null) $incompleteMixed++;
            return $balance ?? 0;
        });
        $installmentOutstanding = $plans->sum(fn ($plan) => $financials->planBalance($plan));
        $unknownTotalPlans = $plans->filter(fn ($plan) => $plan->hasUnknownTotal());
        $monthlyContracts = $plans->filter(fn ($plan) => $plan->is_unpriced_contract);
        $outstandingBalance = $ordinaryOutstanding + $installmentOutstanding;

        return view('admin.patients.show', compact('patient', 'upcoming', 'past', 'procedures', 'ordinaryOutstanding', 'installmentOutstanding', 'outstandingBalance', 'unknownTotalPlans', 'monthlyContracts', 'incompleteMixed'));
    }
}
