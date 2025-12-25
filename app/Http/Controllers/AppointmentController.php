<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['patient', 'service', 'doctor'])
            ->latest()
            ->paginate(10);

        return view('appointments.index', compact('appointments'));
    }

    public function create()
    {
        $patients = Patient::orderBy('first_name')->get();
        $services = Service::orderBy('name')->get();

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

            // If you have doctor_id column in appointments, allow using it
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],

            // Keep dentist_name for compatibility with your UI
            'dentist_name' => ['nullable', 'string', 'max:255'],

            // ✅ allow new statuses
            'status' => ['required', Rule::in([
                'pending', 'approved', 'confirmed',
                'scheduled', 'completed', 'done',
                'canceled', 'cancelled', 'declined', 'rejected'
            ])],

            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // ✅ if doctor_id is selected, auto-fill dentist_name from doctors table
        if (!empty($validated['doctor_id'])) {
            $validated['dentist_name'] = Doctor::whereKey($validated['doctor_id'])->value('name') ?? ($validated['dentist_name'] ?? null);
        }

        Appointment::create($validated);

        return redirect()
            ->route('staff.appointments.index')
            ->with('success', 'Appointment added successfully!');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'service', 'doctor']);

        return view('appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $appointment->load(['patient', 'service', 'doctor']);

        $patients = Patient::orderBy('first_name')->get();
        $services = Service::orderBy('name')->get();

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

            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'dentist_name' => ['nullable', 'string', 'max:255'],

            'status' => ['required', Rule::in([
                'pending', 'approved', 'confirmed',
                'scheduled', 'completed', 'done',
                'canceled', 'cancelled', 'declined', 'rejected'
            ])],

            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if (!empty($validated['doctor_id'])) {
            $validated['dentist_name'] = Doctor::whereKey($validated['doctor_id'])->value('name') ?? ($validated['dentist_name'] ?? null);
        }

        $appointment->update($validated);

        return redirect()
            ->route('staff.appointments.index')
            ->with('success', 'Appointment updated successfully!');
    }

    public function destroy(Appointment $appointment)
    {
        $appointment->delete();

        return redirect()
            ->route('staff.appointments.index')
            ->with('success', 'Appointment deleted successfully!');
    }
}
