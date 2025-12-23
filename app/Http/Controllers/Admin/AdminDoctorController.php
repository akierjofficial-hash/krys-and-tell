<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class AdminDoctorController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));
        $status = $request->get('status', '');

        $doctors = Doctor::query()
            ->when($q, fn($qq) => $qq->where('name', 'like', "%{$q}%")
                                   ->orWhere('email', 'like', "%{$q}%"))
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
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);

        Doctor::create($data);

        return redirect()->route('admin.doctors.index')->with('success', 'Doctor added.');
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
        ]);

        $data['is_active'] = $request->boolean('is_active', false);
        $data['sort_order'] = (int)($data['sort_order'] ?? 0);

        $doctor->update($data);

        return redirect()->route('admin.doctors.index')->with('success', 'Doctor updated.');
    }

    public function toggleActive(Doctor $doctor)
    {
        $doctor->update(['is_active' => !$doctor->is_active]);
        return back()->with('success', 'Doctor status updated.');
    }
}
