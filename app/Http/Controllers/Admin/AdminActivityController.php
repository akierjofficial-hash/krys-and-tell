<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class AdminActivityController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'], 'event' => ['nullable', 'string', 'max:120'],
            'actor' => ['nullable', 'integer'], 'from' => ['nullable', 'date'], 'to' => ['nullable', 'date'],
            'sensitive' => ['nullable', 'boolean'],
            'target_type' => ['nullable', 'string', 'max:120'], 'result' => ['nullable', 'in:success,failure'],
        ]);
        $logs = ActivityLog::with('user')
            ->when($filters['q'] ?? null, fn ($q, $term) => $q->where(fn ($w) => $w->where('description', 'like', "%$term%")
                ->orWhere('event', 'like', "%$term%")->orWhere('reason', 'like', "%$term%")))
            ->when($filters['event'] ?? null, fn ($q, $event) => $q->where('event', $event))
            ->when($filters['actor'] ?? null, fn ($q, $actor) => $q->where('user_id', $actor))
            ->when($request->boolean('sensitive'), fn ($q) => $q->where('is_sensitive', true))
            ->when($filters['target_type'] ?? null, fn ($q, $type) => $q->where('target_type', $type))
            ->when(($filters['result'] ?? null) === 'success', fn ($q) => $q->where('succeeded', true))
            ->when(($filters['result'] ?? null) === 'failure', fn ($q) => $q->where('succeeded', false))
            ->when($filters['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date))
            ->latest('created_at')->paginate(25)->withQueryString();
        $actors = User::withTrashed()->whereIn('role', ['admin', 'staff'])->orderBy('name')->get(['id', 'name']);
        $events = ActivityLog::whereNotNull('event')->distinct()->orderBy('event')->pluck('event');
        $targetTypes = ActivityLog::whereNotNull('target_type')->distinct()->orderBy('target_type')->pluck('target_type');
        return view('admin.activity.index', compact('logs', 'actors', 'events', 'targetTypes', 'filters'));
    }
}
