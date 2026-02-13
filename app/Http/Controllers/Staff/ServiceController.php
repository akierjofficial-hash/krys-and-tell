<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Patient;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::all();
        return view('staff.services.index', compact('services'));
    }

    public function create()
    {
        return view('staff.services.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'base_price'         => 'required|numeric|min:0',
            'allow_custom_price' => 'required|in:0,1',
            'description'        => 'nullable|string',

            // ✅ walk-in fields
            'is_walk_in'         => 'nullable|boolean',
            'walk_in_note'       => 'nullable|string|max:255',

            // ✅ duration only matters for scheduled services
            // clinic said treatments max 1 hour -> max 60
            'duration_minutes'   => 'nullable|integer|min:15|max:60',
        ]);

        $isWalkIn = (bool)($validated['is_walk_in'] ?? false);

        Service::create([
            'name'               => $validated['name'],
            'base_price'         => $validated['base_price'],
            'allow_custom_price' => (int) $validated['allow_custom_price'],
            'description'        => $validated['description'] ?? null,

            'is_walk_in'         => $isWalkIn,
            'walk_in_note'       => $validated['walk_in_note'] ?? null,

            // ✅ if walk-in, ignore duration
            'duration_minutes'   => $isWalkIn ? null : ($validated['duration_minutes'] ?? null),
        ]);

        return $this->ktRedirectToReturn($request, 'staff.services.index')
            ->with('success', 'Service added successfully!');
    }

    public function edit(Service $service)
    {
        return view('staff.services.edit', compact('service'));
    }

    public function update(Request $request, Service $service)
    {
        $validated = $request->validate([
            'name'               => 'required|string|max:255',
            'base_price'         => 'required|numeric|min:0',
            'allow_custom_price' => 'required|in:0,1',
            'description'        => 'nullable|string',

            'is_walk_in'         => 'nullable|boolean',
            'walk_in_note'       => 'nullable|string|max:255',

            'duration_minutes'   => 'nullable|integer|min:15|max:60',
        ]);

        $isWalkIn = (bool)($validated['is_walk_in'] ?? false);

        $service->update([
            'name'               => $validated['name'],
            'base_price'         => $validated['base_price'],
            'allow_custom_price' => (int) $validated['allow_custom_price'],
            'description'        => $validated['description'] ?? null,

            'is_walk_in'         => $isWalkIn,
            'walk_in_note'       => $validated['walk_in_note'] ?? null,

            'duration_minutes'   => $isWalkIn ? null : ($validated['duration_minutes'] ?? null),
        ]);

        return $this->ktRedirectToReturn($request, 'staff.services.index')
            ->with('success', 'Service updated successfully!');
    }

    public function patients(Service $service)
    {
        $patients = Patient::query()
            ->whereHas('visits.procedures', function ($q) use ($service) {
                $q->where('service_id', $service->id);
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        return view('staff.services.patients', compact('service', 'patients'));
    }

    public function restore(Request $request, int $id)
    {
        $service = Service::withTrashed()->findOrFail($id);
        $service->restore();

        return $this->ktRedirectToReturn($request, 'staff.services.index')
            ->with('success', 'Service restored successfully!');
    }

    public function destroy(Request $request, Service $service)
    {
        $name = $service->name ?? ('#'.$service->id);

        $service->delete();

        $returnUrl = $this->ktReturnUrl($request, 'staff.services.index');

        return $this->ktRedirectToReturn($request, 'staff.services.index')
            ->with('success', 'Service deleted successfully!')
            ->with('undo', [
                'message' => 'Service deleted: ' . $name,
                'url' => route('staff.services.restore', ['id' => $service->id, 'return' => $returnUrl]),
                'ms' => 10000,
            ]);
    }
}
