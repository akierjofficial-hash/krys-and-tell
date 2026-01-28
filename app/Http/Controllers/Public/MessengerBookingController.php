<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MessengerBookingController extends Controller
{
    public function create(Request $request)
    {
        // ManyChat query params (can be real, or {{cuf_...}} in preview)
        $serviceText = (string) $request->query('service', '');

        // support both ?time= and ?preferred_time=
        $timeText = (string) ($request->query('time', '') ?: $request->query('preferred_time', ''));

        $services = Service::query()->orderBy('name')->get();

        // Prefill: service -> service_id if looks real
        $prefillServiceId = null;
        if ($this->looksResolved($serviceText)) {
            $prefillServiceId = $this->matchServiceIdFromText($services, $serviceText);
        }

        // Prefill: time -> H:i:s if looks real
        $prefillTime = null;
        if ($this->looksResolved($timeText)) {
            $prefillTime = $this->parseTimeToHms($timeText);
        }

        return view('public.messenger-book', [
            // display-only
            'service' => $serviceText,
            'time' => $timeText,

            // used by dropdowns
            'services' => $services,
            'prefillServiceId' => $prefillServiceId,
            'prefillTime' => $prefillTime,
        ]);
    }

    public function store(Request $request)
    {
        // Dropdown submits H:i:s (like 10:00:00)
        $allowedTimes = [
            '09:00:00','10:00:00','11:00:00','12:00:00',
            '13:00:00','14:00:00','15:00:00','16:00:00','17:00:00'
        ];

        $data = $request->validate([
            'service_id'       => ['required', 'integer', 'exists:services,id'],
            'appointment_time' => ['required', Rule::in($allowedTimes)],
            'preferred_date'   => ['required', 'date', 'after_or_equal:today'],
            'full_name'        => ['required', 'string', 'max:255'],
            'phone'            => ['required', 'string', 'max:50'],
            'notes'            => ['nullable', 'string', 'max:2000'],

            // optional debug (from ManyChat)
            'service_text'     => ['nullable', 'string', 'max:255'],
            'time_text'        => ['nullable', 'string', 'max:255'],
        ]);

        $service = Service::findOrFail($data['service_id']);

        $appt = new Appointment();
        $appt->service_id = $service->id;
        $appt->appointment_date = $data['preferred_date'];
        $appt->appointment_time = $data['appointment_time'];

        // duration is optional (only if column exists in services)
        $appt->duration_minutes = $service->duration_minutes ?? null;

        // ✅ will appear in Staff Approvals
        $appt->status = 'pending';

        // Optional: link logged-in user
        if (auth()->check()) {
            $appt->user_id = auth()->id();
        }

        // Public info (based on your columns)
        $appt->public_name = $data['full_name'];
        $appt->public_phone = $data['phone'];

        $parts = preg_split('/\s+/', trim($data['full_name'])) ?: [];
        $appt->public_first_name = $parts[0] ?? null;
        $appt->public_last_name = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

        // Notes: keep raw ManyChat text for troubleshooting
        $noteLines = [];
        $noteLines[] = 'Booked via Messenger form.';
        $noteLines[] = 'Selected service_id: ' . $service->id . ' (' . $service->name . ')';
        $noteLines[] = 'Selected time: ' . $data['appointment_time'];

        if (!empty($data['service_text'])) $noteLines[] = 'Service text (raw): ' . $data['service_text'];
        if (!empty($data['time_text'])) $noteLines[] = 'Time text (raw): ' . $data['time_text'];
        if (!empty($data['notes'])) $noteLines[] = 'Notes: ' . $data['notes'];

        $appt->notes = implode("\n", $noteLines);

        $appt->save();

        return redirect()->route('messenger.book.success');
    }

    public function success()
    {
        return view('public.messenger-book-success');
    }

    private function parseTimeToHms(?string $input): ?string
    {
        $input = trim((string) $input);
        if ($input === '') return null;

        try {
            $dt = Carbon::parse('today ' . $input);
            return $dt->format('H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function looksResolved(string $v): bool
    {
        $v = trim($v);
        if ($v === '') return false;
        if (str_contains($v, '{{') || str_contains($v, 'cuf_')) return false;
        return true;
    }

    private function matchServiceIdFromText($services, string $text): ?int
    {
        // Normalize (Braces/Adjustment -> bracesadjustment)
        $needle = strtolower(preg_replace('/[^a-z0-9]+/i', '', $text));

        foreach ($services as $s) {
            $hay = strtolower(preg_replace('/[^a-z0-9]+/i', '', $s->name));
            if ($hay === $needle) return $s->id;
        }

        foreach ($services as $s) {
            $hay = strtolower(preg_replace('/[^a-z0-9]+/i', '', $s->name));
            if (str_contains($needle, $hay) || str_contains($hay, $needle)) return $s->id;
        }

        return null;
    }
}
