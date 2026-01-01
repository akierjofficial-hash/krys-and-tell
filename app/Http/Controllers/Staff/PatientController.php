<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Patient;

class PatientController extends Controller
{
    public function index()
    {
        $patients = Patient::all();

        return view('staff.patients.index', compact('patients'));
    }

    public function create()
    {
        return view('staff.patients.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'middle_name'    => 'nullable|string|max:255',
            'birthdate'      => 'required|date',
            'gender'         => 'required|string|in:Male,Female,Other',
            'contact_number' => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:500',
            'notes'          => 'nullable|string|max:1000',
        ]);

        // ✅ If staff didn't click "Create Anyway", run duplicate check
        $forceCreate = $request->boolean('force_create');

        if (!$forceCreate) {
            $first   = mb_strtolower(trim($validated['first_name']));
            $last    = mb_strtolower(trim($validated['last_name']));
            $birth   = $validated['birthdate'] ?? null;
            $contact = trim($validated['contact_number'] ?? '');

            $dupesQuery = Patient::query()
                ->whereRaw('LOWER(first_name) = ? AND LOWER(last_name) = ?', [$first, $last])
                ->when($birth, fn ($q) => $q->whereDate('birthdate', $birth));

            // If contact was provided, also check contact matches
            if ($contact !== '') {
                $dupesQuery->orWhere('contact_number', $contact);
            }

            $dupes = $dupesQuery
                ->orderByDesc('created_at')
                ->take(5)
                ->get(['id', 'first_name', 'middle_name', 'last_name', 'birthdate', 'contact_number']);

            if ($dupes->isNotEmpty()) {
                return back()
                    ->withInput()
                    ->with('duplicate_candidates', $dupes);
            }
        }

        Patient::create($validated);

        return redirect()
            ->route('staff.patients.index')
            ->with('success', 'Patient added successfully!');
    }

    public function show(Patient $patient)
    {
        $visits = $patient->visits()
            ->orderByDesc('visit_date')
            ->paginate(10, ['*'], 'visits_page');

        $appointments = $patient->appointments()
            ->with('service')
            ->orderByDesc('appointment_date')
            ->orderByDesc('appointment_time')
            ->paginate(10, ['*'], 'appointments_page');

        $payments = $patient->payments()
            ->with('visit')
            ->orderByDesc('payment_date')
            ->paginate(10, ['*'], 'payments_page');

        $totalPaid = $patient->payments()->sum('amount');

        return view('staff.patients.show', compact(
            'patient',
            'visits',
            'appointments',
            'payments',
            'totalPaid'
        ));
    }

    public function edit(Patient $patient)
    {
        return view('staff.patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'first_name'     => 'required|string|max:255',
            'last_name'      => 'required|string|max:255',
            'middle_name'    => 'nullable|string|max:255',
            'birthdate'      => 'nullable|date',
            'gender'         => 'nullable|string|max:10',
            'contact_number' => 'nullable|string|max:20',
            'address'        => 'nullable|string|max:500',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $patient->update($validated);

        return redirect()
            ->route('staff.patients.index')
            ->with('success', 'Patient updated successfully!');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();

        return redirect()
            ->route('staff.patients.index')
            ->with('success', 'Patient deleted successfully!');
    }
}
