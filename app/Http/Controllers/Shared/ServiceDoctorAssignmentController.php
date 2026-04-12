<?php

namespace App\Http\Controllers\Shared;

use App\Http\Controllers\Controller;
use App\Models\Doctor;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class ServiceDoctorAssignmentController extends Controller
{
    public function index(Request $request)
    {
        $portal = $this->portalConfig($request);
        $search = trim((string) $request->query('q', ''));
        $schemaReady = Schema::hasTable('doctor_service')
            && Schema::hasColumn('services', 'restrict_to_assigned_doctors');

        $servicesQuery = Service::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%');
            })
            ->orderBy('name');

        if ($schemaReady) {
            $servicesQuery->with(['assignedDoctors' => function ($query) {
                if (Schema::hasColumn('doctors', 'is_active')) {
                    $query->orderByDesc('is_active');
                }

                if (Schema::hasColumn('doctors', 'name')) {
                    $query->orderBy('name');
                } else {
                    $query->orderBy('id');
                }
            }]);
        }

        $services = $servicesQuery->get();

        if (!$schemaReady) {
            $services->each(function ($service) {
                $service->restrict_to_assigned_doctors = false;
                $service->setRelation('assignedDoctors', collect());
            });
        }

        $doctors = Schema::hasTable('doctors')
            ? Doctor::query()
                ->when(Schema::hasColumn('doctors', 'is_active'), function ($query) {
                    $query->orderByDesc('is_active');
                })
                ->when(Schema::hasColumn('doctors', 'name'), function ($query) {
                    $query->orderBy('name');
                }, function ($query) {
                    $query->orderBy('id');
                })
                ->get()
            : collect();

        return view('shared.service-doctor-assignments.index', [
            'layout' => $portal['layout'],
            'routePrefix' => $portal['routePrefix'],
            'portalLabel' => $portal['label'],
            'services' => $services,
            'doctors' => $doctors,
            'search' => $search,
            'assignmentServiceId' => session('assignment_service_id'),
            'schemaReady' => $schemaReady,
        ]);
    }

    public function update(Request $request, Service $service)
    {
        $portal = $this->portalConfig($request);
        $schemaReady = Schema::hasTable('doctor_service')
            && Schema::hasColumn('services', 'restrict_to_assigned_doctors');

        if (!$schemaReady) {
            return redirect()
                ->route($portal['routePrefix'] . '.service_doctor_assignments.index')
                ->with('error', 'Treatment-doctor assignments are not ready yet. Run the latest migration first.');
        }

        $validator = Validator::make($request->all(), [
            'restrict_to_assigned_doctors' => ['nullable', 'boolean'],
            'doctor_ids' => ['nullable', 'array'],
            'doctor_ids.*' => ['integer', 'exists:doctors,id'],
        ]);

        $validator->after(function ($validator) use ($request) {
            $restrict = $request->boolean('restrict_to_assigned_doctors');
            $doctorIds = collect($request->input('doctor_ids', []))
                ->filter(fn ($value) => $value !== null && $value !== '')
                ->all();

            if ($restrict && count($doctorIds) === 0) {
                $validator->errors()->add('doctor_ids', 'Select at least one doctor when restriction is enabled.');
            }

            if (
                $restrict
                && count($doctorIds) > 0
                && Schema::hasTable('doctors')
                && Schema::hasColumn('doctors', 'is_active')
            ) {
                $activeCount = Doctor::query()
                    ->whereIn('id', $doctorIds)
                    ->where('is_active', true)
                    ->count();

                if ($activeCount === 0) {
                    $validator->errors()->add('doctor_ids', 'At least one assigned doctor must be active before this treatment can be restricted.');
                }
            }
        });

        if ($validator->fails()) {
            return back()
                ->withErrors($validator)
                ->withInput()
                ->with('assignment_service_id', $service->id);
        }

        $doctorIds = collect($request->input('doctor_ids', []))
            ->map(fn ($value) => (int) $value)
            ->filter()
            ->unique()
            ->values()
            ->all();

        if (Schema::hasColumn('services', 'restrict_to_assigned_doctors')) {
            $service->restrict_to_assigned_doctors = $request->boolean('restrict_to_assigned_doctors');
            $service->save();
        }

        if (Schema::hasTable('doctor_service')) {
            $service->assignedDoctors()->sync($doctorIds);
        }

        $message = $service->restrict_to_assigned_doctors
            ? 'Assigned doctors saved. Public booking will now follow this treatment-specific doctor list.'
            : 'Assignments saved. This treatment still allows all active doctors until restriction is turned on.';

        return redirect()
            ->route($portal['routePrefix'] . '.service_doctor_assignments.index')
            ->with('success', $message)
            ->with('assignment_service_id', $service->id);
    }

    private function portalConfig(Request $request): array
    {
        if ($request->routeIs('admin.*')) {
            return [
                'layout' => 'layouts.admin',
                'routePrefix' => 'admin',
                'label' => 'Admin',
            ];
        }

        return [
            'layout' => 'layouts.staff',
            'routePrefix' => 'staff',
            'label' => 'Staff',
        ];
    }
}
