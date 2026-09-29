<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Doctor;
use App\Services\FinancialService;

class VisitController extends Controller
{
    public function index(Request $request)
    {
        // Toggle: show "All Visits" (old behavior) when ?view=all
        $view = $request->query('view', 'patients');

        if ($view === 'all') {
            $request->validate([
                'date_from' => ['nullable', 'date'],
                'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            ]);

            $visits = Visit::with([
                    'patient',
                    'procedures.service',
                    'doctor',
                ])
                ->when($request->filled('date_from'), fn ($query) => $query->whereDate('visit_date', '>=', $request->date_from))
                ->when($request->filled('date_to'), fn ($query) => $query->whereDate('visit_date', '<=', $request->date_to))
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
    if ($request->filled('patient_id')) {
        return redirect()->route('staff.records.index', ['patient_id' => $request->patient_id, 'mode' => 'visit']);
    }

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

        return $this->ktRedirectToReturn($request, 'staff.visits.index')
            ->with('success', 'Visit created successfully.');
    }

    public function show(Visit $visit, FinancialService $finance)
    {
        $visit->load(['patient', 'doctor', 'procedures.service']);
        $visitCharge = $finance->visitCharge($visit);
        return view('staff.visits.show', compact('visit', 'visitCharge'));
    }

    public function edit(Visit $visit)
    {
        $patients = Patient::orderBy('first_name')->get();
        $services = Service::orderBy('name')->get();

        $doctors = Doctor::where('is_active', 1)->orWhere('id', $visit->doctor_id)
            ->orderBy('name')
            ->get(['id','name','specialty']);

        $visit->load(['doctor', 'procedures.service']);

        $procedurePayload = $visit->procedures->map(fn ($p) => [
            'id'          => $p->id,
            'price'       => $p->price,
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
            'notes'      => 'nullable|string|max:2000',

            'procedures' => 'required|array|min:1',
            'procedures.*.id' => ['nullable', 'integer', 'distinct', \Illuminate\Validation\Rule::exists('visit_procedures', 'id')->where('visit_id', $visit->id)],
            'procedures.*.service_id'   => 'required|exists:services,id',
            'procedures.*.tooth_number' => 'nullable|string|max:50',
            'procedures.*.surface'      => 'nullable|string|max:10',
            'procedures.*.shade'        => 'nullable|string|max:10',
            'procedures.*.notes'        => 'nullable|string|max:2000',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $visit) {
        $visit = Visit::whereKey($visit->id)->lockForUpdate()->firstOrFail();
        $doctor = Doctor::findOrFail($validated['doctor_id']);
        $existing = $visit->procedures()->get();
        $kept = [];
        $visit->update([
            'patient_id'   => $validated['patient_id'],
            'doctor_id'    => $doctor->id,
            'dentist_name' => $doctor->name, 
            'visit_date'   => $validated['visit_date'],
            'notes'        => $validated['notes'] ?? null,
        ]);

        foreach ($validated['procedures'] as $procedure) {
            $service = Service::find($procedure['service_id']);
            $saved = !empty($procedure['id']) ? $existing->firstWhere('id', $procedure['id']) : $existing->first(fn ($p) => !in_array($p->id, $kept) && $p->service_id == $procedure['service_id'] && (string) $p->tooth_number === (string) ($procedure['tooth_number'] ?? ''));
            $attributes = [
                'service_id'   => $procedure['service_id'],
                'tooth_number' => $procedure['tooth_number'] ?? null,
                'surface'      => $procedure['surface'] ?? null,
                'shade'        => $procedure['shade'] ?? null,
                'notes'        => $procedure['notes'] ?? null,
                'price'        => $saved ? $saved->price : ($service?->base_price ?? 0),
            ];
            if ($saved) {
                $saved->update($attributes);
            } else {
                $saved = $visit->procedures()->create($attributes);
            }
            $kept[] = $saved->id;
        }
        $visit->procedures()->whereNotIn('id', $kept)->delete();
        });

        return $this->ktRedirectToReturn($request, 'staff.visits.index')
            ->with('success', 'Visit updated successfully!');
    }

    public function restore(Request $request, int $id)
    {
        $visit = Visit::withTrashed()->findOrFail($id);
        $visit->restore();

        return $this->ktRedirectToReturn($request, 'staff.visits.index')
            ->with('success', 'Visit restored successfully!');
    }

    public function destroy(Request $request, Visit $visit)
    {
        $label = 'Visit #' . $visit->id;
        if (!empty($visit->visit_date)) {
            try { $label .= ' (' . \Carbon\Carbon::parse($visit->visit_date)->format('M d, Y') . ')'; } catch (\Throwable $e) {}
        }

        $visit->delete();

        $returnUrl = $this->ktReturnUrl($request, 'staff.visits.index');

        return $this->ktRedirectToReturn($request, 'staff.visits.index')
            ->with('success', 'Visit deleted successfully!')
            ->with('undo', [
                'message' => $label . ' deleted.',
                'url' => route('staff.visits.restore', ['id' => $visit->id, 'return' => $returnUrl]),
                'ms' => 10000,
            ]);
    }
}
