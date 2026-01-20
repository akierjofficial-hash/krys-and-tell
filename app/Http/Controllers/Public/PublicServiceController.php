<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PublicServiceController extends Controller
{
    /**
     * Show public services list
     * Route: public.services.index
     */
    public function index(Request $request)
    {
        // Keep it safe with schema checks (since your project uses Schema guards)
        if (!Schema::hasTable('services')) {
            return view('public.services.index', [
                'services' => collect(),
            ]);
        }

        $q = Service::query();

        // Optional "active" filtering if your table has it
        if (Schema::hasColumn('services', 'is_active')) {
            $q->where('is_active', 1);
        }

        // Search support if you have search UI
        if ($request->filled('q')) {
            $term = trim((string) $request->q);

            if ($term !== '' && Schema::hasColumn('services', 'name')) {
                $q->where('name', 'like', '%' . $term . '%');
            }
        }

        // Sorting
        if (Schema::hasColumn('services', 'name')) {
            $q->orderBy('name');
        } else {
            $q->orderBy('id', 'desc');
        }

        $services = $q->get();

        return view('public.services.index', [
            'services' => $services,
        ]);
    }

    /**
     * Show a single service page
     * Route: public.services.show
     */
    public function show(Service $service)
    {
        return view('public.services.show', [
            'service' => $service,
        ]);
    }
}
