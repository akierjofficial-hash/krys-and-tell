<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Doctor;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['patient', 'service'])
            ->latest('id')
            ->paginate(15);

        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $patients = Patient::orderBy('first_name')->get();
        $services = Service::orderBy('name')->get();

        // ✅ Pull from admin doctors table (active only)
        $doctors = Doctor::where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'specialty']);

        return view('appointments.create', compact('patients', 'services', 'doctors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'service_id' => ['required', 'exists:services,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required'],
            'dentist_name' => [
                'required',
                'string',
                'max:255',
                // ✅ must exist in doctors table AND be active
                Rule::exists('doctors', 'name')->where(fn ($q) => $q->where('is_active', 1)),
            ],
            'status' => ['required', Rule::in(['scheduled', 'completed', 'canceled'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        Appointment::create($validated);

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment added successfully!');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'service']);
        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $patients = Patient::orderBy('first_name')->get();
        $services = Service::orderBy('name')->get();

        // ✅ same list for edit page
        $doctors = Doctor::where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'specialty']);

        return view('appointments.edit', compact('appointment', 'patients', 'services', 'doctors'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'service_id' => ['required', 'exists:services,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required'],
            'dentist_name' => [
                'required',
                'string',
                'max:255',
                Rule::exists('doctors', 'name')->where(fn ($q) => $q->where('is_active', 1)),
            ],
            'status' => ['required', Rule::in(['scheduled', 'completed', 'canceled'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $appointment->update($validated);

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment updated successfully!');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()
            ->route('appointments.index')
            ->with('success', 'Appointment deleted successfully!');
    }
}
