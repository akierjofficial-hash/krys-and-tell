<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Schema;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'q' => ['nullable', 'string', 'max:200'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
            'sort' => ['nullable', Rule::in(['dt_desc', 'dt_asc', 'patient_asc', 'patient_desc', 'dentist_asc', 'dentist_desc', 'status_asc', 'status_desc'])],
        ]);

        $search = trim((string) $request->query('q', ''));
        $sort = $request->query('sort', 'dt_desc');
        $appointments = Appointment::with(['patient', 'service', 'doctor'])
            ->leftJoin('patients as sort_patient', 'sort_patient.id', '=', 'appointments.patient_id')
            ->leftJoin('doctors as sort_doctor', 'sort_doctor.id', '=', 'appointments.doctor_id')
            ->select('appointments.*')
            ->when($search !== '', function ($query) use ($search) {
                $parts = preg_split('/[\s,]+/u', $search, -1, PREG_SPLIT_NO_EMPTY) ?: [];
                $query->where(function ($matching) use ($search, $parts) {
                    $matching->whereHas('patient', function ($patient) use ($parts) {
                        foreach ($parts as $part) {
                            $patient->where(fn ($name) => $name->whereLike('first_name', "%{$part}%")
                                ->orWhereLike('middle_name', "%{$part}%")
                                ->orWhereLike('last_name', "%{$part}%"));
                        }
                    })->orWhere(function ($public) use ($parts) {
                        foreach ($parts as $part) {
                            $public->where(fn ($name) => $name->whereLike('public_first_name', "%{$part}%")
                                ->orWhereLike('public_middle_name', "%{$part}%")
                                ->orWhereLike('public_last_name', "%{$part}%")
                                ->orWhereLike('public_name', "%{$part}%"));
                        }
                    })->orWhereHas('service', fn ($service) => $service->whereLike('name', "%{$search}%"))
                        ->orWhereHas('doctor', fn ($doctor) => $doctor->whereLike('name', "%{$search}%"))
                        ->orWhereLike('appointments.dentist_name', "%{$search}%")
                        ->orWhereLike('appointments.status', "%{$search}%");
                });
            })
            ->when($request->filled('date_from'), fn ($query) => $query->whereDate('appointments.appointment_date', '>=', $request->date_from))
            ->when($request->filled('date_to'), fn ($query) => $query->whereDate('appointments.appointment_date', '<=', $request->date_to))
            ->when(in_array($sort, ['patient_asc', 'patient_desc'], true), function ($query) use ($sort) {
                $direction = $sort === 'patient_asc' ? 'asc' : 'desc';
                $query->orderByRaw("LOWER(COALESCE(sort_patient.last_name, appointments.public_last_name, appointments.public_name, '')) {$direction}")
                    ->orderByRaw("LOWER(COALESCE(sort_patient.first_name, appointments.public_first_name, '')) {$direction}");
            })
            ->when(in_array($sort, ['dentist_asc', 'dentist_desc'], true), function ($query) use ($sort) {
                $direction = $sort === 'dentist_asc' ? 'asc' : 'desc';
                $query->orderByRaw("LOWER(COALESCE(appointments.dentist_name, sort_doctor.name, '')) {$direction}");
            })
            ->when(in_array($sort, ['status_asc', 'status_desc'], true), function ($query) use ($sort) {
                $query->orderBy('appointments.status', $sort === 'status_asc' ? 'asc' : 'desc');
            })
            ->when(in_array($sort, ['dt_asc', 'dt_desc'], true), function ($query) use ($sort) {
                $direction = $sort === 'dt_asc' ? 'asc' : 'desc';
                $query->orderBy('appointments.appointment_date', $direction)
                    ->orderBy('appointments.appointment_time', $direction);
            })
            ->orderByDesc('appointments.id')
            ->paginate(10)
            ->withQueryString();

        return view('staff.appointments.index', compact('appointments'));
    }

    public function create(Request $request)
    {
        $request->validate(['patient_id' => ['nullable', Rule::exists('patients', 'id')->whereNull('deleted_at')]]);
        $patients = Patient::when($request->filled('patient_id'), fn ($q) => $q->whereKey($request->patient_id))->orderBy('first_name')->get();
        $services = Service::publiclyAvailable()->orderBy('name')->get();

        $doctors = Doctor::where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'specialty']);

        return view('staff.appointments.create', compact('patients', 'services', 'doctors'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'service_id' => ['required', Rule::exists('services', 'id')->where('is_staff_only', 0)->whereNull('deleted_at')],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required'],

            // doctor_id preferred; dentist_name fallback supported
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'dentist_name' => ['nullable', 'string', 'max:255'],

            'status' => ['required', Rule::in([
                'pending', 'approved', 'confirmed',
                'scheduled', 'completed', 'done',
                'walked_in',
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

        // Keep the notification email copy without inferring website ownership.
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
        abort_if($appointment->status === 'voided', 404);
        $appointment->load(['patient', 'service', 'doctor']);

        $patients = Patient::orderBy('first_name')->get();
        $services = Service::publiclyAvailable()->orderBy('name')->get();

        $doctors = Doctor::where('is_active', 1)
            ->orderBy('name')
            ->get(['id', 'name', 'specialty']);

        return view('staff.appointments.edit', compact('appointment', 'patients', 'services', 'doctors'));
    }

    public function update(Request $request, Appointment $appointment)
    {
        if ($appointment->status === 'voided') {
            return back()->withErrors(['appointment' => 'Voided requests are retained as internal history.']);
        }
        $validated = $request->validate([
            'patient_id' => ['required', 'exists:patients,id'],
            'service_id' => ['required', Rule::exists('services', 'id')->where('is_staff_only', 0)->whereNull('deleted_at')],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required'],

            // doctor_id preferred; dentist_name fallback supported
            'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
            'dentist_name' => ['nullable', 'string', 'max:255'],

            'status' => ['required', Rule::in([
                'pending', 'approved', 'confirmed',
                'scheduled', 'completed', 'done',
                'walked_in',
                'canceled', 'cancelled', 'declined', 'rejected'
            ])],

            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($appointment->status === 'pending' && $validated['status'] !== 'pending') {
            return back()->withErrors(['status' => 'Use Booking Requests to approve, decline, or void a pending request.']);
        }

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

        // Keep the notification email copy without changing booking ownership.
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
        if (in_array($appointment->status, ['pending', 'voided'], true)) {
            return back()->withErrors(['appointment' => 'Use Booking Requests to decide a pending request. Voided requests must remain in history.']);
        }
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

    /** Keep the appointment's notification contact copy in sync. */
    private function syncAppointmentPublicLink(Appointment $appointment): void
    {
        $patient = Patient::find($appointment->patient_id);
        if (!$patient) {
            return;
        }

        $patientEmail = $patient->email ?? null;
        if (!empty($patientEmail) && Schema::hasColumn('appointments', 'public_email')) {
            $appointment->public_email = $patientEmail;
            $appointment->save();
        }
    }
}
