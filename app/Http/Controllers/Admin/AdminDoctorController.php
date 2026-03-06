<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AdminDoctorController extends Controller
{
    private const DEFAULT_WORKING_DAYS = [1, 2, 3, 4, 5, 6]; // Mon-Sat
    private const DEFAULT_WORK_START = '09:00';
    private const DEFAULT_WORK_END = '17:00';

    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = $request->get('status', '');

        $doctors = Doctor::query()
            ->when($q, function ($qq) use ($q) {
                // Group OR conditions so they don't escape other filters
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%")
                      ->orWhere('email', 'like', "%{$q}%");
                });
            })
            ->when($status !== '', fn($qq) => $qq->where('is_active', $status === 'active'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        return view('admin.doctors.index', compact('doctors','q','status'));
    }

    public function create()
    {
        return view('admin.doctors.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'phone' => ['nullable','string','max:50'],
            'specialty' => ['nullable','string','max:255'],
            'sort_order' => ['nullable','integer','min:0','max:65535'],
            'is_active' => ['nullable'],
            'working_days' => ['nullable', 'array'],
            'working_days.*' => ['integer', Rule::in([1, 2, 3, 4, 5, 6, 7])],
            'work_start_time' => ['nullable', 'date_format:H:i'],
            'work_end_time' => ['nullable', 'date_format:H:i'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);
        $data = array_merge($data, $this->schedulePayload($request));

        Doctor::create($data);

        return $this->ktRedirectToReturn($request, 'admin.doctors.index')
            ->with('success', 'Doctor added.');
    }

    public function edit(Doctor $doctor)
    {
        return view('admin.doctors.edit', compact('doctor'));
    }

    public function update(Request $request, Doctor $doctor)
    {
        $data = $request->validate([
            'name' => ['required','string','max:255'],
            'email' => ['nullable','email','max:255'],
            'phone' => ['nullable','string','max:50'],
            'specialty' => ['nullable','string','max:255'],
            'sort_order' => ['nullable','integer','min:0','max:65535'],
            'is_active' => ['nullable'],
            'working_days' => ['nullable', 'array'],
            'working_days.*' => ['integer', Rule::in([1, 2, 3, 4, 5, 6, 7])],
            'work_start_time' => ['nullable', 'date_format:H:i'],
            'work_end_time' => ['nullable', 'date_format:H:i'],
        ]);

        $data['is_active'] = $request->boolean('is_active', false);
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);
        $data = array_merge($data, $this->schedulePayload($request));

        $doctor->update($data);

        return $this->ktRedirectToReturn($request, 'admin.doctors.index')
            ->with('success', 'Doctor updated.');
    }

    public function toggleActive(Request $request, Doctor $doctor)
    {
        $doctor->update(['is_active' => !$doctor->is_active]);
        return $this->ktRedirectToReturn($request, 'admin.doctors.index')
            ->with('success', 'Doctor status updated.');
    }

    private function schedulePayload(Request $request): array
    {
        $workingDays = collect((array) $request->input('working_days', self::DEFAULT_WORKING_DAYS))
            ->map(fn ($d) => (int) $d)
            ->filter(fn ($d) => $d >= 1 && $d <= 7)
            ->unique()
            ->sort()
            ->values()
            ->all();

        if (empty($workingDays)) {
            $workingDays = self::DEFAULT_WORKING_DAYS;
        }

        $startRaw = trim((string) $request->input('work_start_time', self::DEFAULT_WORK_START));
        $endRaw = trim((string) $request->input('work_end_time', self::DEFAULT_WORK_END));

        if ($startRaw === '') $startRaw = self::DEFAULT_WORK_START;
        if ($endRaw === '') $endRaw = self::DEFAULT_WORK_END;

        $start = Carbon::createFromFormat('H:i', $startRaw);
        $end = Carbon::createFromFormat('H:i', $endRaw);

        if (!$end->gt($start)) {
            throw ValidationException::withMessages([
                'work_end_time' => 'Work end time must be later than work start time.',
            ]);
        }

        return [
            'working_days' => $workingDays,
            'work_start_time' => $start->format('H:i:s'),
            'work_end_time' => $end->format('H:i:s'),
        ];
    }
}
