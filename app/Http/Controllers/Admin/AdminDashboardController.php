<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use App\Services\FinancialService;

class AdminDashboardController extends Controller
{
    public function index(FinancialService $financials)
    {
        $now = now(config('app.timezone'));
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();
        $money = $financials->summary();
        $pendingRequests = Appointment::where('status', 'pending')->count();
        $newPatients = Patient::whereBetween('created_at', [$monthStart, $monthEnd])->count();
        $accountAlerts = collect();
        if (User::where('role', 'admin')->where('is_active', true)->count() === 1) {
            $accountAlerts->push(['title' => 'Only one active administrator remains', 'detail' => 'Create or activate another administrator for account recovery.', 'url' => route('admin.users.index')]);
        }
        $deletedCount = User::onlyTrashed()->count();
        if ($deletedCount) $accountAlerts->push(['title' => "$deletedCount deleted account(s) can be restored", 'detail' => 'Review accounts retained in the recovery area.', 'url' => route('admin.deleted_accounts.index')]);
        $recentAccountChanges = ActivityLog::whereIn('event', ['account.status_changed', 'account.updated', 'account.deleted'])
            ->where('created_at', '>=', $now->copy()->subDays(7))->count();
        if ($recentAccountChanges) $accountAlerts->push(['title' => "$recentAccountChanges sensitive account change(s) this week", 'detail' => 'Review who made each change and the recorded reason.', 'url' => route('admin.activity.index', ['sensitive' => 1])]);
        $securityAlerts = $accountAlerts->count();
        $oldestRequests = Appointment::with(['service', 'doctor', 'patient'])->where('status', 'pending')->oldest()->limit(5)->get();
        $nearestAppointments = Appointment::with(['service', 'doctor', 'patient'])
            ->whereIn('status', Appointment::STATUS_ACTIVE)->whereDate('appointment_date', '>=', $now->toDateString())
            ->orderBy('appointment_date')->orderBy('appointment_time')->limit(6)->get();
        $recentSensitiveActivity = ActivityLog::with('user')->where('is_sensitive', true)->latest('created_at')->limit(6)->get();
        $collectionLabels = [];
        $collectionValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = $now->copy()->subDays($i);
            $collectionLabels[] = $day->format('D');
            $collectionValues[] = $financials->collectedOn($day);
        }
        return view('admin.dashboard', compact('now', 'money', 'pendingRequests', 'newPatients', 'securityAlerts',
            'oldestRequests', 'nearestAppointments', 'recentSensitiveActivity', 'collectionLabels', 'collectionValues', 'accountAlerts'));
    }
}
