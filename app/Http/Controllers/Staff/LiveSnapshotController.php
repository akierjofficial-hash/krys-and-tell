<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\Visit;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;

class LiveSnapshotController extends Controller
{
    /**
     * Lightweight endpoint for AJAX polling.
     *
     * Returns a stable key that changes when underlying data changes.
     * Frontend can compare the key and decide whether to reload.
     */
    public function snapshot(Request $request)
    {
        $scope = (string) $request->query('scope', '');
        $scope = trim($scope);

        $payload = match ($scope) {
            'patients'     => $this->snapModel(Patient::query()),
            'appointments' => $this->snapAppointments(),
            'visits'       => $this->snapModel(Visit::query()),
            'services'     => $this->snapModel(Service::query()),
            'payments'     => $this->snapPaymentsAndInstallments(),
            'installments' => $this->snapInstallments(),
            'messages'     => $this->snapMessages(),
            'approvals'    => $this->snapApprovals(),
            default        => null,
        };

        if (!$payload) {
            return response()->json(['ok' => false, 'message' => 'Invalid scope.'], 404);
        }

        return response()->json(['ok' => true, 'scope' => $scope] + $payload);
    }

    private function snapModel($query): array
    {
        $count = (int) $query->count();

        $maxUpdated = $query->max('updated_at');
        $ts = $maxUpdated ? Carbon::parse($maxUpdated)->timestamp : 0;

        return [
            'count' => $count,
            'latest_updated_at' => $maxUpdated ? Carbon::parse($maxUpdated)->toIso8601String() : null,
            'key' => $count . ':' . $ts,
        ];
    }

    private function snapAppointments(): array
    {
        $q = Appointment::query();

        $count = (int) $q->count();
        $maxUpdated = $q->max('updated_at');
        $ts = $maxUpdated ? Carbon::parse($maxUpdated)->timestamp : 0;

        // Also include pending approvals count (optional) so staff pages can update quickly.
        $pending = 0;
        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'status')) {
            $pending = (int) Appointment::query()->where('status', 'pending')->count();
        }

        return [
            'count' => $count,
            'pending' => $pending,
            'latest_updated_at' => $maxUpdated ? Carbon::parse($maxUpdated)->toIso8601String() : null,
            'key' => $count . ':' . $ts . ':' . $pending,
        ];
    }

    private function snapApprovals(): array
    {
        $q = Appointment::query();

        if (Schema::hasTable('appointments') && Schema::hasColumn('appointments', 'status')) {
            $q->where('status', 'pending');
        }

        $count = (int) $q->count();
        $maxUpdated = $q->max('updated_at');
        $ts = $maxUpdated ? Carbon::parse($maxUpdated)->timestamp : 0;

        return [
            'count' => $count,
            'latest_updated_at' => $maxUpdated ? Carbon::parse($maxUpdated)->toIso8601String() : null,
            'key' => $count . ':' . $ts,
        ];
    }

    private function snapMessages(): array
    {
        $q = ContactMessage::query();

        $count = (int) $q->count();
        $maxUpdated = $q->max('updated_at');
        $ts = $maxUpdated ? Carbon::parse($maxUpdated)->timestamp : 0;

        $unread = 0;
        if (Schema::hasTable('contact_messages') && Schema::hasColumn('contact_messages', 'is_read')) {
            $unread = (int) ContactMessage::query()->where('is_read', false)->count();
        }

        return [
            'count' => $count,
            'unread' => $unread,
            'latest_updated_at' => $maxUpdated ? Carbon::parse($maxUpdated)->toIso8601String() : null,
            'key' => $count . ':' . $ts . ':' . $unread,
        ];
    }

    private function snapInstallments(): array
    {
        $q = InstallmentPlan::query();

        $count = (int) $q->count();
        $maxUpdated = $q->max('updated_at');
        $ts = $maxUpdated ? Carbon::parse($maxUpdated)->timestamp : 0;

        $paymentsMax = InstallmentPayment::query()->max('updated_at');
        $pts = $paymentsMax ? Carbon::parse($paymentsMax)->timestamp : 0;

        // include both so editing payments triggers a refresh
        return [
            'count' => $count,
            'latest_updated_at' => $maxUpdated ? Carbon::parse($maxUpdated)->toIso8601String() : null,
            'key' => $count . ':' . max($ts, $pts),
        ];
    }

    private function snapPaymentsAndInstallments(): array
    {
        $payCount = (int) Payment::query()->count();
        $instCount = (int) InstallmentPlan::query()->count();

        $maxUpdatedPayments = Payment::query()->max('updated_at');
        $maxUpdatedPlans = InstallmentPlan::query()->max('updated_at');
        $maxUpdatedInstPay = InstallmentPayment::query()->max('updated_at');

        $ts1 = $maxUpdatedPayments ? Carbon::parse($maxUpdatedPayments)->timestamp : 0;
        $ts2 = $maxUpdatedPlans ? Carbon::parse($maxUpdatedPlans)->timestamp : 0;
        $ts3 = $maxUpdatedInstPay ? Carbon::parse($maxUpdatedInstPay)->timestamp : 0;

        $maxTs = max($ts1, $ts2, $ts3);

        return [
            'count' => $payCount + $instCount,
            'payments' => $payCount,
            'installment_plans' => $instCount,
            'latest_updated_at' => $maxTs ? Carbon::createFromTimestamp($maxTs)->toIso8601String() : null,
            'key' => ($payCount + $instCount) . ':' . $maxTs . ':' . $payCount . ':' . $instCount,
        ];
    }
}
