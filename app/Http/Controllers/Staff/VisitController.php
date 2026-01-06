<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Doctor;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        // Toggle: show "All Visits" (old behavior) when ?view=all
        $view = $request->query('view', 'patients');

        if ($view === 'all') {
            $visits = Visit::with([
                    'patient',
                    'procedures.service',
                    'doctor',
                ])
                ->orderByDesc('visit_date')
                ->orderByDesc('created_at')
                ->get();

            return view('staff.visits.index', compact('view', 'visits'));
        }

        // Default: show UNIQUE patients list (one row per patient)
        // Default: show ONLY patients that have visits (unique list)
$patients = Patient::query()
    ->whereHas('visits') // ✅ only patients with at least 1 visit
    ->select('patients.*')
    ->withCount('visits')
    ->withMax('visits as last_visit_date', 'visit_date')
    ->orderByDesc('last_visit_date')
    ->orderBy('last_name')
    ->orderBy('first_name')
    ->get();

return view('staff.visits.index', compact('view', 'patients'));

    }

    public function patientVisits(Patient $patient)
    {
        $visits = Visit::with([
                'patient',
                'procedures.service',
                'doctor',
            ])
            ->where('patient_id', $patient->id)
            ->orderByDesc('visit_date')
            ->orderByDesc('created_at')
            ->get();

        return view('staff.visits.patient', compact('patient', 'visits'));
    }


    public function create(Request $request)
{
    $patients = Patient::orderBy('last_name')->orderBy('first_name')->get();

    // ✅ Load doctors for "Assigned Dentist" dropdown
    // If you have an "active" column, keep it. If not, remove the where().
    $doctors = Doctor::query()
        ->when(\Schema::hasColumn('doctors', 'status'), fn($q) => $q->where('status', 'Active'))
        ->when(\Schema::hasColumn('doctors', 'is_active'), fn($q) => $q->where('is_active', 1))
        ->orderBy('name')
        ->get();

    // ✅ Load services for procedures dropdown
    $services = Service::orderBy('name')->get();

    // ✅ Patient preselect via ?patient_id=
    $preselectedPatientId = $request->query('patient_id');
    $preselectedPatient = null;

    if ($preselectedPatientId) {
        $preselectedPatient = Patient::find($preselectedPatientId);
        if (!$preselectedPatient) {
            $preselectedPatientId = null;
        }
    }

    return view('staff.visits.create', compact(
        'patients',
        'doctors',
        'services',
        'preselectedPatientId',
        'preselectedPatient'
    ));
}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id'  => 'required|exists:doctors,id',
            'visit_date' => 'required|date',
            'notes'      => 'nullable|string|max:1000',

            'procedures' => 'required|array|min:1',
            'procedures.*.service_id'   => 'required|exists:services,id',
            'procedures.*.tooth_number' => 'nullable|string|max:10',
            'procedures.*.surface'      => 'nullable|string|max:10',
            'procedures.*.shade'        => 'nullable|string|max:10',
            'procedures.*.notes'        => 'nullable|string|max:1000',
        ]);

        $doctor = Doctor::findOrFail($validated['doctor_id']);

        $visit = Visit::create([
            'patient_id'   => $validated['patient_id'],
            'doctor_id'    => $doctor->id,
            'dentist_name' => $doctor->name, 
            'visit_date'   => $validated['visit_date'],
            'notes'        => $validated['notes'] ?? null,
        ]);

        foreach ($validated['procedures'] as $procedure) {
            $service = Service::find($procedure['service_id']);

            $visit->procedures()->create([
                'service_id'   => $procedure['service_id'],
                'tooth_number' => $procedure['tooth_number'] ?? null,
                'surface'      => $procedure['surface'] ?? null,
                'shade'        => $procedure['shade'] ?? null,
                'notes'        => $procedure['notes'] ?? null,
                'price'        => $service?->base_price ?? 0,
            ]);
        }

        return redirect()->route('staff.visits.index')
            ->with('success', 'Visit created successfully.');
    }

    public function show(Visit $visit)
    {
        $visit->load(['patient', 'doctor', 'procedures.service']);
        return view('staff.visits.show', compact('visit'));
    }

    public function edit(Visit $visit)
    {
        $patients = Patient::orderBy('first_name')->get();
        $services = Service::orderBy('name')->get();

        $doctors = Doctor::where('is_active', 1)
            ->orderBy('name')
            ->get(['id','name','specialty']);

        $visit->load(['doctor', 'procedures.service']);

        $procedurePayload = $visit->procedures->map(fn ($p) => [
            'service_id'   => $p->service_id,
            'service_name' => $p->service?->name,
            'tooth_number' => $p->tooth_number,
            'surface'      => $p->surface,
            'shade'        => $p->shade,
            'notes'        => $p->notes,
        ])->values();

        return view('staff.visits.edit', compact('visit', 'patients', 'services', 'doctors', 'procedurePayload'));
    }

    public function update(Request $request, Visit $visit)
    {
        $validated = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'doctor_id'  => 'required|exists:doctors,id',
            'visit_date' => 'required|date',
            'notes'      => 'nullable|string|max:1000',

            'procedures' => 'required|array|min:1',
            'procedures.*.service_id'   => 'required|exists:services,id',
            'procedures.*.tooth_number' => 'nullable|string|max:10',
            'procedures.*.surface'      => 'nullable|string|max:10',
            'procedures.*.shade'        => 'nullable|string|max:10',
            'procedures.*.notes'        => 'nullable|string|max:1000',
        ]);

        $doctor = Doctor::findOrFail($validated['doctor_id']);

        $visit->update([
            'patient_id'   => $validated['patient_id'],
            'doctor_id'    => $doctor->id,
            'dentist_name' => $doctor->name, 
            'visit_date'   => $validated['visit_date'],
            'notes'        => $validated['notes'] ?? null,
        ]);

        $visit->procedures()->delete();

        foreach ($validated['procedures'] as $procedure) {
            $service = Service::find($procedure['service_id']);

            $visit->procedures()->create([
                'service_id'   => $procedure['service_id'],
                'tooth_number' => $procedure['tooth_number'] ?? null,
                'surface'      => $procedure['surface'] ?? null,
                'shade'        => $procedure['shade'] ?? null,
                'notes'        => $procedure['notes'] ?? null,
                'price'        => $service?->base_price ?? 0,
            ]);
        }

        return redirect()->route('staff.visits.index')
            ->with('success', 'Visit updated successfully!');
    }

    public function destroy(Visit $visit)
    {
        $visit->delete();
        return redirect()->route('staff.visits.index')->with('success', 'Visit deleted successfully!');
    }
}
