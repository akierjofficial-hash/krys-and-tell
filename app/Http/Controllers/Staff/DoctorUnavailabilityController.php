<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\DoctorUnavailability;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DoctorUnavailabilityController extends Controller
{
    public function index(Request $request)
    {
        $selectedDoctorId = $request->filled('doctor_id') ? (int) $request->input('doctor_id') : null;
        $fromDate = trim((string) $request->input('from_date', now()->toDateString()));

        try {
            $fromDate = Carbon::parse($fromDate)->toDateString();
        } catch (\Throwable $e) {
            $fromDate = now()->toDateString();
        }

        $doctors = Doctor::query()
            ->when(Schema::hasColumn('doctors', 'is_active'), fn ($q) => $q->where('is_active', 1))
            ->orderBy('name')
            ->get(['id', 'name', 'specialty']);

        $items = DoctorUnavailability::query()
            ->with(['doctor:id,name,specialty'])
            ->when($selectedDoctorId, fn ($q) => $q->where('doctor_id', $selectedDoctorId))
            ->whereDate('unavailable_date', '>=', $fromDate)
            ->orderBy('unavailable_date')
            ->orderBy('doctor_id')
            ->paginate(20)
            ->withQueryString();

        return view('shared.dentist_unavailability.index', [
            'layout' => 'layouts.staff',
            'routePrefix' => 'staff.dentist-unavailability',
            'pageTitle' => 'Dentist Day-off',
            'pageSubtitle' => 'Set dentist unavailable dates for meetings, leave, and events. Blocked dentists will not be bookable on selected dates.',
            'doctors' => $doctors,
            'items' => $items,
            'selectedDoctorId' => $selectedDoctorId,
            'fromDate' => $fromDate,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'unavailable_date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $date = Carbon::parse($data['unavailable_date'])->toDateString();

        DoctorUnavailability::query()->updateOrCreate(
            [
                'doctor_id' => (int) $data['doctor_id'],
                'unavailable_date' => $date,
            ],
            [
                'reason' => isset($data['reason']) ? trim((string) $data['reason']) : null,
                'created_by' => auth()->id(),
            ]
        );

        return $this->ktRedirectToReturn($request, 'staff.dentist-unavailability.index')
            ->with('success', 'Dentist unavailable date saved.');
    }

    public function update(Request $request, DoctorUnavailability $doctorUnavailability)
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'integer', 'exists:doctors,id'],
            'unavailable_date' => ['required', 'date', 'after_or_equal:today'],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        $doctorId = (int) $data['doctor_id'];
        $date = Carbon::parse($data['unavailable_date'])->toDateString();

        $duplicate = DoctorUnavailability::query()
            ->where('doctor_id', $doctorId)
            ->whereDate('unavailable_date', $date)
            ->where('id', '!=', $doctorUnavailability->id)
            ->exists();

        if ($duplicate) {
            return back()
                ->withInput()
                ->with('editing_id', $doctorUnavailability->id)
                ->withErrors([
                    'unavailable_date' => 'That dentist already has an unavailable record on this date.',
                ]);
        }

        $doctorUnavailability->doctor_id = $doctorId;
        $doctorUnavailability->unavailable_date = $date;
        $doctorUnavailability->reason = isset($data['reason']) ? trim((string) $data['reason']) : null;
        if (empty($doctorUnavailability->created_by)) {
            $doctorUnavailability->created_by = auth()->id();
        }
        $doctorUnavailability->save();

        return $this->ktRedirectToReturn($request, 'staff.dentist-unavailability.index')
            ->with('success', 'Dentist unavailable date updated.');
    }

    public function destroy(Request $request, DoctorUnavailability $doctorUnavailability)
    {
        $doctorUnavailability->delete();

        return $this->ktRedirectToReturn($request, 'staff.dentist-unavailability.index')
            ->with('success', 'Dentist unavailable date removed.');
    }
}
