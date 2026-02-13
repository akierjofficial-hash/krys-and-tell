<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with(['patient', 'service', 'doctor'])
            ->latest()
            ->paginate(10);

        return view('staff.appointments.index', compact('appointments'));
    }

    public function create()
    {
        $patients = Patient::orderBy('first_name')->get();
        $services = Service::orderBy('name')->get();

        $doctors = Doctor::where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'specialty']);

        return view('staff.appointments.create', compact('patients', 'services', 'doctors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'service_id' => ['required', 'exists:services,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required'],

            // doctor_id preferred; dentist_name fallback supported
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'dentist_name' => ['nullable', 'string', 'max:255'],

            'status' => ['required', Rule::in([
                'pending', 'approved', 'confirmed',
                'scheduled', 'completed', 'done',
                'canceled', 'cancelled', 'declined', 'rejected'
            ])],

            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Require at least one dentist identifier
        if (empty($validated['doctor_id']) && empty($validated['dentist_name'])) {
            return back()
                ->withErrors(['doctor_id' => 'Please choose a dentist (doctor) or enter a dentist name.'])
                ->withInput();
        }

        // Sync dentist_name from doctor_id when present
        if (!empty($validated['doctor_id'])) {
            $validated['dentist_name'] = Doctor::whereKey($validated['doctor_id'])->value('name')
                ?? ($validated['dentist_name'] ?? null);
        }

        $appointment = Appointment::create($validated);

        // Link appointment to public user (optional but helps patient see it)
        $this->syncAppointmentPublicLink($appointment);

        return $this->ktRedirectToReturn($request, 'staff.appointments.index')
            ->with('success', 'Appointment added successfully!');
    }

    public function show(Appointment $appointment)
    {
        $appointment->load(['patient', 'service', 'doctor']);

        return view('staff.appointments.show', compact('appointment'));
    }

    public function edit(Appointment $appointment)
    {
        $appointment->load(['patient', 'service', 'doctor']);

        $patients = Patient::orderBy('first_name')->get();
        $services = Service::orderBy('name')->get();

        $doctors = Doctor::where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'specialty']);

        return view('staff.appointments.edit', compact('appointment', 'patients', 'services', 'doctors'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'service_id' => ['required', 'exists:services,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required'],

            // doctor_id preferred; dentist_name fallback supported
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'dentist_name' => ['nullable', 'string', 'max:255'],

            'status' => ['required', Rule::in([
                'pending', 'approved', 'confirmed',
                'scheduled', 'completed', 'done',
                'canceled', 'cancelled', 'declined', 'rejected'
            ])],

            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        // Require at least one dentist identifier
        if (empty($validated['doctor_id']) && empty($validated['dentist_name'])) {
            return back()
                ->withErrors(['doctor_id' => 'Please choose a dentist (doctor) or enter a dentist name.'])
                ->withInput();
        }

        // Sync dentist_name from doctor_id when present
        if (!empty($validated['doctor_id'])) {
            $validated['dentist_name'] = Doctor::whereKey($validated['doctor_id'])->value('name')
                ?? ($validated['dentist_name'] ?? null);
        }

        $appointment->update($validated);

        // ✅ Ensure patient/public side can see latest doctor/date/time changes
        $this->syncAppointmentPublicLink($appointment);

        return $this->ktRedirectToReturn($request, 'staff.appointments.index')
            ->with('success', 'Appointment updated successfully!');
    }

    public function restore(Request $request, int $id)
    {
        $appointment = Appointment::withTrashed()->findOrFail($id);
        $appointment->restore();

        return $this->ktRedirectToReturn($request, 'staff.appointments.index')
            ->with('success', 'Appointment restored successfully!');
    }

    public function destroy(Request $request, Appointment $appointment)
    {
        $label = 'Appointment #' . $appointment->id;
        if (!empty($appointment->appointment_date)) {
            try { $label .= ' (' . \Carbon\Carbon::parse($appointment->appointment_date)->format('M d, Y') . ')'; } catch (\Throwable $e) {}
        }

        $appointment->delete();

        $returnUrl = $this->ktReturnUrl($request, 'staff.appointments.index');

        return $this->ktRedirectToReturn($request, 'staff.appointments.index')
            ->with('success', 'Appointment deleted successfully!')
            ->with('undo', [
                'message' => $label . ' deleted.',
                'url' => route('staff.appointments.restore', ['id' => $appointment->id, 'return' => $returnUrl]),
                'ms' => 10000,
            ]);
    }

    /**
     * Link appointment to the patient’s public account (user_id + public_email),
     * so it appears correctly in the patient/public profile.
     */
    private function syncAppointmentPublicLink(Appointment $appointment): void
    {
        // Load patient
        $patient = Patient::find($appointment->patient_id);
        if (!$patient) return;

        $patientEmail = $patient->email ?? null;
        if (empty($patientEmail)) return;

        // If appointments has public_email, keep it synced to patient email
        if (Schema::hasColumn('appointments', 'public_email')) {
            $appointment->public_email = $patientEmail;
        }

        // If appointments has user_id, try to match user by email
        if (Schema::hasColumn('appointments', 'user_id')) {
            // If patient has user_id column and it’s set, use it
            if (Schema::hasColumn('patients', 'user_id') && !empty($patient->user_id)) {
                $appointment->user_id = $patient->user_id;
            } else {
                $userId = User::where('email', $patientEmail)->value('id');
                if (!empty($userId)) {
                    $appointment->user_id = $userId;
                }
            }
        }

        $appointment->save();
    }
}
