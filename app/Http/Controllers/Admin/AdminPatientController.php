<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminPatientController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $patients = Patient::query()
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($qq) use ($q) {
                    $qq->where('first_name', 'like', "%{$q}%")
                       ->orWhere('last_name', 'like', "%{$q}%")
                       ->orWhere('middle_name', 'like', "%{$q}%")
                       ->orWhere('contact_number', 'like', "%{$q}%")
                       ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12)
            ->withQueryString();

        return view('admin.patients.index', compact('patients', 'q'));
    }

    public function show(Patient $patient)
    {
        $patient->load(['files']);

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

        return view('admin.patients.show', compact('patient', 'upcoming', 'past', 'procedures'));
    }
}
