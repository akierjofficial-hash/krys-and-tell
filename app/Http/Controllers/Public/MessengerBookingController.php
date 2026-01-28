<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Http\Request;

class MessengerBookingController extends Controller
{
    public function create(Request $request)
    {
        $service = (string) $request->query('service', '');
        $preferredTime = (string) $request->query('time', '');

        return view('public.messenger-book', [
            'service' => $service,
            'preferredTime' => $preferredTime,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'service_name'    => ['required', 'string', 'max:255'],
            'preferred_date'  => ['required', 'date'],
            'preferred_time'  => ['nullable', 'string', 'max:50'], // can come from ManyChat query
            'full_name'       => ['required', 'string', 'max:255'],
            'phone'           => ['required', 'string', 'max:50'],
            'notes'           => ['nullable', 'string', 'max:2000'],
        ]);

        $serviceName = trim($data['service_name']);

        // Try to match a real service
        $service = Service::query()
            ->where('name', $serviceName)
            ->orWhere('name', 'like', '%' . $serviceName . '%')
            ->first();

        $time = $this->parseTimeToHms($data['preferred_time'] ?? null);

        $appt = new Appointment();
        $appt->service_id = $service?->id; // nullable ok
        $appt->appointment_date = $data['preferred_date'];
        $appt->appointment_time = $time;   // nullable ok
        $appt->duration_minutes = $service?->duration_minutes; // nullable ok
        $appt->status = 'pending';

        // Save public info (your real columns)
        $appt->public_name = $data['full_name'];
        $appt->public_phone = $data['phone'];

        // Optional split for your other columns
        $parts = preg_split('/\s+/', trim($data['full_name'])) ?: [];
        $appt->public_first_name = $parts[0] ?? null;
        $appt->public_last_name = count($parts) > 1 ? implode(' ', array_slice($parts, 1)) : null;

        $noteLines = [];
        $noteLines[] = 'Booked via Messenger form.';
        $noteLines[] = 'Service text: ' . $serviceName;
        if (!empty($data['preferred_time'])) {
            $noteLines[] = 'Preferred time (raw): ' . $data['preferred_time'];
        }
        if (!empty($data['notes'])) {
            $noteLines[] = 'Notes: ' . $data['notes'];
        }
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
            // Works for "9 AM", "10 AM", "9:30 AM", "14:00", etc.
            $dt = Carbon::parse('today ' . $input);
            return $dt->format('H:i:s');
        } catch (\Throwable $e) {
            return null;
        }
    }
}
