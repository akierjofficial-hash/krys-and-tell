<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Patient;
use App\Models\Payment;           // <-- This is required
use App\Models\InstallmentPlan;   // <-- This is required

class PatientController extends Controller
{
    public function index()
{
    // Get all patients
    $patients = Patient::all();

    // Pass to the correct view
    return view('patients.index', compact('patients'));
}


    public function store(Request $request)
    {
        $validated = $request->validate([
    'first_name' => 'required|string|max:255',
    'last_name' => 'required|string|max:255',
    'middle_name' => 'nullable|string|max:255',
    'birthdate' => 'required|date',
    'gender' => 'required|string|in:Male,Female,Other', // ✅ require gender
    'contact_number' => 'nullable|string|max:20',
    'address' => 'nullable|string|max:500',
    'notes' => 'nullable|string|max:1000',
]);


        Patient::create($validated);

        return redirect()->route('staff.patients.index')->with('success', 'Patient added successfully!');
    }

    public function edit(Patient $patient)
    {
        return view('patients.edit', compact('patient'));
    }

    public function update(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'birthdate' => 'nullable|date',
            'gender' => 'nullable|string|max:10',
            'contact_number' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        $patient->update($validated);

        return redirect()->route('staff.patients.index')->with('success', 'Patient updated successfully!');
    }

    public function destroy(Patient $patient)
    {
        $patient->delete();
        return redirect()->route('staff.patients.index')->with('success', 'Patient deleted successfully!');
    }

    public function show(Patient $patient)
    {
        // Paginated history lists (each has its own query-string key)
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

        // Totals (overall, not just the current page)
        $totalPaid = $patient->payments()->sum('amount');

        return view('patients.show', compact(
            'patient',
            'visits',
            'appointments',
            'payments',
            'totalPaid'
        ));
    }
    public function create()
{
    // Just return the create view
    return view('patients.create');
}

    
}
