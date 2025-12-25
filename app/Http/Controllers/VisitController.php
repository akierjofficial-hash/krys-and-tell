<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Visit;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Doctor;

class VisitController extends Controller
{
    public function index()
    {
        $visits = Visit::with(['patient', 'doctor', 'procedures.service'])->get();
        return view('visits.index', compact('visits'));
    }

    public function create()
    {
        $patients = Patient::orderBy('first_name')->get();
        $services = Service::orderBy('name')->get();

        // ✅ pull from admin doctors table (active only)
        $doctors = Doctor::where('is_active', 1)
            ->orderBy('name')
            ->get(['id','name','specialty']);

        return view('visits.create', compact('patients', 'services', 'doctors'));
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
            'dentist_name' => $doctor->name, // snapshot
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
        return view('visits.show', compact('visit'));
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

        return view('visits.edit', compact('visit', 'patients', 'services', 'doctors', 'procedurePayload'));
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
            'dentist_name' => $doctor->name, // snapshot update
            'visit_date'   => $validated['visit_date'],
            'notes'        => $validated['notes'] ?? null,
        ]);

        // Replace procedures (simple & reliable)
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
