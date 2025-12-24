<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PublicBookingController extends Controller
{
    public function create(Service $service)
    {
        $doctors = $this->activeDoctors();

        return view('public.booking.create', [
            'service' => $service,
            'doctors' => $doctors,
        ]);
    }

    public function slots(Request $request, Service $service)
    {
        $doctorRequired = $this->doctorRequired();

        $request->validate([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'doctor_id' => $doctorRequired ? ['required', 'integer', 'exists:doctors,id'] : ['nullable', 'integer'],
        ]);

        $date = Carbon::parse($request->date)->toDateString();
        $doctorId = $doctorRequired ? $request->integer('doctor_id') : ($request->integer('doctor_id') ?: null);

        $slots = $this->computeSlots($date, $doctorId);

        return response()->json([
            'date' => $date,
            'doctor_id' => $doctorId,
            'slots' => $slots,
        ]);
    }

    public function store(Request $request, Service $service)
    {
        $doctorRequired = $this->doctorRequired();

        // Decide if gender/birthdate must be required based on DB
        $genderRules = $this->rulesIfRequiredColumn('patients', 'gender', ['string', 'max:40']);
        $birthdateRules = $this->rulesIfRequiredColumn('patients', 'birthdate', ['date', 'before_or_equal:today']);

        $request->validate(array_merge([
            'date' => ['required', 'date', 'after_or_equal:today'],
            'time' => ['required', 'date_format:H:i'],

            'doctor_id' => $doctorRequired
                ? ['required', 'integer', 'exists:doctors,id']
                : ['nullable', 'integer', 'exists:doctors,id'],

            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:190'],
            'contact' => ['required', 'string', 'max:40'],
            'message' => ['nullable', 'string', 'max:500'],
        ], $genderRules, $birthdateRules));

        $date = Carbon::parse($request->date)->toDateString();
        $time = $request->time;

        $doctorId = $doctorRequired ? $request->integer('doctor_id') : ($request->integer('doctor_id') ?: null);

        // ✅ Ensure slot still available (prevents double booking)
        $available = in_array($time, $this->computeSlots($date, $doctorId), true);
        if (!$available) {
            return back()
                ->withErrors(['time' => 'That time slot is no longer available. Please choose another.'])
                ->withInput();
        }

        return DB::transaction(function () use ($request, $service, $date, $time, $doctorId) {

            $patientId = $this->upsertPatient(
                $request->name,
                $request->email,
                $request->contact,
                $request->input('gender'),
                $request->input('birthdate')
            );

            $appointment = new Appointment();

            if (Schema::hasColumn('appointments', 'patient_id')) $appointment->patient_id = $patientId;
            if (Schema::hasColumn('appointments', 'service_id')) $appointment->service_id = $service->id;

            if (Schema::hasColumn('appointments', 'appointment_date')) $appointment->appointment_date = $date;
            if (Schema::hasColumn('appointments', 'appointment_time')) $appointment->appointment_time = $time;

            // ✅ Save doctor_id if supported
            if ($doctorId && Schema::hasColumn('appointments', 'doctor_id')) {
                $appointment->doctor_id = $doctorId;
            }

            // ✅ Save dentist_name for staff UI (fixes "N/A")
            if ($doctorId && Schema::hasColumn('appointments', 'dentist_name')) {
                $appointment->dentist_name = Doctor::whereKey($doctorId)->value('name');
            }

            // Store public data on appointment if columns exist
            if (Schema::hasColumn('appointments', 'public_name')) $appointment->public_name = $request->name;
            if (Schema::hasColumn('appointments', 'public_email')) $appointment->public_email = $request->email;
            if (Schema::hasColumn('appointments', 'public_phone')) $appointment->public_phone = $request->contact;
            if (Schema::hasColumn('appointments', 'public_message')) $appointment->public_message = $request->message;

            if (Schema::hasColumn('appointments', 'status')) $appointment->status = 'pending';

            $appointment->save();

            return redirect()->route('public.booking.success', $appointment);
        });
    }

    public function success(Appointment $appointment)
    {
        $appointment->loadMissing(['service', 'patient', 'doctor']);

        return view('public.booking.success', compact('appointment'));
    }

    private function computeSlots(string $date, ?int $doctorId): array
    {
        $day = Carbon::parse($date)->dayOfWeekIso;
        if ($day === 7) return [];

        $start = Carbon::parse($date . ' 09:00');
        $end   = Carbon::parse($date . ' 18:00');
        $stepMinutes = 30;

        if (!Schema::hasColumn('appointments', 'appointment_time')) {
            return [];
        }

        $bookedQuery = Appointment::query();

        if (Schema::hasColumn('appointments', 'appointment_date')) {
            $bookedQuery->whereDate('appointment_date', $date);
        }

        if (Schema::hasColumn('appointments', 'status')) {
            $bookedQuery->where(function ($q) {
                $q->whereNull('status')
                    ->orWhereNotIn('status', ['cancelled', 'canceled']);
            });
        }

        if ($doctorId && Schema::hasColumn('appointments', 'doctor_id')) {
            $bookedQuery->where('doctor_id', $doctorId);
        }

        $booked = $bookedQuery->pluck('appointment_time')->map(function ($t) {
            try {
                return Carbon::parse($t)->format('H:i');
            } catch (\Throwable $e) {
                return (string) $t;
            }
        })->unique()->values()->all();

        $slots = [];
        $cursor = $start->copy();

        while ($cursor->lt($end)) {
            $label = $cursor->format('H:i');
            if (!in_array($label, $booked, true)) $slots[] = $label;
            $cursor->addMinutes($stepMinutes);
        }

        return $slots;
    }

    private function activeDoctors()
    {
        if (!class_exists(Doctor::class) || !Schema::hasTable('doctors')) return collect();

        $q = Doctor::query();

        if (Schema::hasColumn('doctors', 'is_active')) $q->where('is_active', 1);

        if (Schema::hasColumn('doctors', 'name')) $q->orderBy('name');
        else $q->orderBy('id');

        return $q->get();
    }

    private function doctorRequired(): bool
    {
        return Schema::hasTable('doctors')
            && Schema::hasColumn('appointments', 'doctor_id')
            && $this->activeDoctors()->count() > 0;
    }

    /**
     * If column is NOT NULL and has no default -> require it, else optional
     * ✅ Driver-aware (MySQL/Postgres/SQLite) so Render(Postgres) won't 500.
     */
    private function rulesIfRequiredColumn(string $table, string $column, array $baseRules): array
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) return [];

        $info = $this->columnInfo($table, $column);

        // If we can't read info, be safe: optional
        if (!$info) {
            return [$column => array_merge(['nullable'], $baseRules)];
        }

        $nullable = $info['nullable'];
        $hasDefault = $info['has_default'];

        if (!$nullable && !$hasDefault) {
            return [$column => array_merge(['required'], $baseRules)];
        }

        return [$column => array_merge(['nullable'], $baseRules)];
    }

    private function resolveRequiredFallback(string $table, string $column, ?string $input, string $fallback): ?string
    {
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) return null;

        $input = trim((string) $input);
        if ($input !== '') return $input;

        $info = $this->columnInfo($table, $column);

        // If we can't read info, just use fallback (safe for required columns)
        if (!$info) {
            return $fallback;
        }

        if ($info['nullable']) return null;
        if ($info['default'] !== null) return (string) $info['default'];

        return $fallback;
    }

    /**
     * Returns: ['nullable'=>bool, 'default'=>mixed, 'has_default'=>bool]
     * Supports mysql / pgsql / sqlite
     */
    private function columnInfo(string $table, string $column): ?array
    {
        $driver = DB::getDriverName();

        // PostgreSQL (Render)
        if ($driver === 'pgsql') {
            $row = DB::selectOne(
                "select is_nullable, column_default
                 from information_schema.columns
                 where table_schema = current_schema()
                   and table_name = ?
                   and column_name = ?
                 limit 1",
                [$table, $column]
            );

            if (!$row) return null;

            $nullable = isset($row->is_nullable) && strtoupper($row->is_nullable) === 'YES';
            $default = $row->column_default ?? null;

            return [
                'nullable' => $nullable,
                'default' => $default,
                'has_default' => $default !== null,
            ];
        }

        // MySQL / MariaDB
        if ($driver === 'mysql') {
            $row = DB::selectOne(
                "select IS_NULLABLE as is_nullable, COLUMN_DEFAULT as column_default
                 from information_schema.columns
                 where table_schema = DATABASE()
                   and table_name = ?
                   and column_name = ?
                 limit 1",
                [$table, $column]
            );

            if (!$row) return null;

            $nullable = isset($row->is_nullable) && strtoupper($row->is_nullable) === 'YES';
            $default = $row->column_default ?? null;

            return [
                'nullable' => $nullable,
                'default' => $default,
                'has_default' => $default !== null,
            ];
        }

        // SQLite
        if ($driver === 'sqlite') {
            $rows = DB::select("PRAGMA table_info($table)");
            foreach ($rows as $r) {
                if ($r->name === $column) {
                    $nullable = ((int) $r->notnull) !== 1;
                    $default = $r->dflt_value ?? null;

                    return [
                        'nullable' => $nullable,
                        'default' => $default,
                        'has_default' => $default !== null,
                    ];
                }
            }
            return null;
        }

        return null;
    }

    private function upsertPatient(string $fullName, string $email, string $contact, ?string $gender, ?string $birthdate): ?int
    {
        if (!class_exists(Patient::class) || !Schema::hasTable('patients')) return null;

        $first = $fullName;
        $last = null;

        $parts = preg_split('/\s+/', trim($fullName));
        if ($parts && count($parts) >= 2) {
            $last = array_pop($parts);
            $first = implode(' ', $parts);
        }

        $existing = null;
        if (Schema::hasColumn('patients', 'email')) {
            $existing = Patient::where('email', $email)->first();
        }

        $patient = $existing ?: new Patient();

        if (Schema::hasColumn('patients', 'first_name')) $patient->first_name = $first;
        if (Schema::hasColumn('patients', 'last_name')) $patient->last_name = $last;

        if (Schema::hasColumn('patients', 'name') && empty($patient->name)) $patient->name = $fullName;
        if (Schema::hasColumn('patients', 'email')) $patient->email = $email;

        foreach (['contact', 'contact_number', 'phone', 'mobile'] as $col) {
            if (Schema::hasColumn('patients', $col)) {
                $patient->{$col} = $contact;
                break;
            }
        }

        // ✅ gender (fallback if required)
        if (Schema::hasColumn('patients', 'gender')) {
            $patient->gender = $this->resolveRequiredFallback('patients', 'gender', $gender, 'Prefer not to say');
        }

        // ✅ birthdate (fallback if required)
        if (Schema::hasColumn('patients', 'birthdate')) {
            $patient->birthdate = $this->resolveRequiredFallback('patients', 'birthdate', $birthdate, '2000-01-01');
        }

        $patient->save();

        return $patient->id;
    }
}
