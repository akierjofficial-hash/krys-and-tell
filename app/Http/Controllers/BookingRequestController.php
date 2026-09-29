<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Services\BookingApprovalService;
use App\Services\BookingKind;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

abstract class BookingRequestController extends Controller
{
    protected string $scope;

    public function index()
    {
        $requests = Appointment::with(['patient', 'service', 'doctor'])->where('status', 'pending')->latest()->paginate(12);
        $doctors = Doctor::active()->orderBy('name')->get();
        $doctorRequired = $doctors->isNotEmpty();
        return view($this->scope.'.approvals.index', compact('requests', 'doctors', 'doctorRequired'));
    }

    public function widget(Request $request)
    {
        $limit = max(1, min(20, (int) $request->query('limit', 6)));
        $query = Appointment::with(['patient', 'service', 'doctor'])->where('status', 'pending')->latest();
        $items = (clone $query)->limit($limit)->get()->map(function (Appointment $a) {
            $walkIn = BookingKind::isWalkIn($a);
            $date = $a->appointment_date ? Carbon::parse($a->appointment_date) : null;
            $time = $a->appointment_time ? Carbon::parse($a->appointment_time) : null;
            return [
                'id' => $a->id,
                'patient' => $a->displayPatientName(),
                'service' => $a->service?->name ?: 'Service not set',
                'doctor' => $a->displayDentistName(),
                'email' => $a->public_email ?: 'Not provided',
                'phone' => $a->public_phone ?: 'Not provided',
                'address' => $a->public_address ?: 'Not provided',
                'date' => $date?->format('M d, Y') ?: 'Not set yet',
                'time' => $walkIn ? 'Walk-in · no reserved time' : ($time?->format('h:i A') ?: 'Not set yet'),
                'service_id' => $a->service_id,
                'doctor_id' => $a->doctor_id,
                'date_raw' => $date?->toDateString(),
                'time_raw' => $time?->format('H:i'),
                'is_walk_in_request' => $walkIn,
                'origin' => BookingKind::origin($a),
                'staff_note' => $a->staff_note ?: '',
                'approve_url' => route($this->scope.'.approvals.approve', $a),
                'decline_url' => route($this->scope.'.approvals.decline', $a),
                'void_url' => route($this->scope.'.approvals.void', $a),
                'candidates_url' => route($this->scope.'.approvals.patients', $a),
                'index_url' => route($this->scope.'.approvals.index'),
            ];
        })->values();
        return response()->json(['pendingCount' => (clone $query)->count(), 'items' => $items]);
    }

    public function patients(Appointment $appointment, BookingApprovalService $approvals)
    {
        abort_unless($appointment->status === 'pending', 404);
        $items = $approvals->patientCandidates($appointment)->map(fn ($patient) => [
            'id' => $patient->id,
            'name' => trim($patient->first_name.' '.($patient->middle_name ?: '').' '.$patient->last_name),
            'birthdate' => $patient->birthdate,
            'contact' => $patient->contact_number,
            'email' => $patient->email,
        ]);
        return response()->json(['items' => $items]);
    }

    public function approve(Request $request, Appointment $appointment, BookingApprovalService $approvals)
    {
        return $this->decision($request, function () use ($request, $appointment, $approvals) {
            $data = $request->validate([
                'doctor_id' => ['nullable', 'integer', 'exists:doctors,id'],
                'appointment_date' => ['nullable', 'date', 'after_or_equal:today'],
                'appointment_time' => ['nullable', 'date_format:H:i'],
                'staff_note' => ['nullable', 'string', 'max:2000'],
                'patient_id' => ['nullable', 'string', 'max:20'],
            ]);
            $approved = $approvals->approve($appointment, $data, $request->user());
            return $approved->status === 'walked_in' ? 'Walk-in request approved; no time was reserved.' : 'Booking approved.';
        });
    }

    public function decline(Request $request, Appointment $appointment, BookingApprovalService $approvals)
    {
        return $this->decision($request, function () use ($request, $appointment, $approvals) {
            $data = $request->validate(['staff_note' => ['required', 'string', 'max:500']]);
            $approvals->decline($appointment, $data['staff_note'], $request->user());
            return 'Booking declined and patient notified.';
        });
    }

    public function void(Request $request, Appointment $appointment, BookingApprovalService $approvals)
    {
        return $this->decision($request, function () use ($request, $appointment, $approvals) {
            $data = $request->validate(['reason' => ['required', 'string', 'max:1000']]);
            $approvals->void($appointment, $data['reason'], $request->user());
            return 'Request voided internally. No patient notification was sent.';
        });
    }

    private function decision(Request $request, callable $action)
    {
        try {
            $message = $action();
            if ($request->expectsJson()) return response()->json([
                'ok' => true, 'message' => $message,
                'pendingCount' => Appointment::where('status', 'pending')->count(),
            ]);
            return $this->ktRedirectToReturn($request, $this->scope.'.approvals.index')->with('success', $message);
        } catch (ValidationException $e) {
            if ($request->expectsJson()) return response()->json(['ok' => false, 'message' => collect($e->errors())->flatten()->first(), 'errors' => $e->errors()], 422);
            return $this->ktRedirectToReturn($request, $this->scope.'.approvals.index')->with('error', collect($e->errors())->flatten()->first());
        }
    }
}
