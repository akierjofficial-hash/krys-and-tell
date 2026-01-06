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
        ]);

        Service::create([
            'name'               => $validated['name'],
            'base_price'         => $validated['base_price'],
            'allow_custom_price' => (int) $validated['allow_custom_price'], 
            'description'        => $validated['description'] ?? null,     
        ]);

        return redirect()->route('staff.services.index')->with('success', 'Service added successfully!');
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
        ]);

        $service->update([
            'name'               => $validated['name'],
            'base_price'         => $validated['base_price'],
            'allow_custom_price' => (int) $validated['allow_custom_price'], 
            'description'        => $validated['description'] ?? null,      
        ]);

        return redirect()->route('staff.services.index')->with('success', 'Service updated successfully!');
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

    public function destroy(Service $service)
    {
        $service->delete();
        return redirect()->route('staff.services.index')->with('success', 'Service deleted successfully!');
    }
}
