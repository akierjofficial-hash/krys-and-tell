<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PublicServiceController extends Controller
{
    public function index()
    {
        $q = Service::query();

        // If you have an "is_active" column, only show active services
        if (Schema::hasColumn('services', 'is_active')) {
            $q->where('is_active', 1);
        }

        $services = $q->orderBy('name')->get();

        return view('public.services.index', compact('services'));
    }

    public function show(Service $service)
    {
        // If you have "is_active", block inactive services
        if (Schema::hasColumn('services', 'is_active') && (int)($service->is_active ?? 1) === 0) {
            abort(404);
        }

        return view('public.services.show', compact('service'));
    }
}
