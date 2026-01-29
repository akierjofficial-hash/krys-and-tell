<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class LiveSnapshotController extends Controller
{
    public function snapshot(Request $request)
    {
        $scope = (string) $request->query('scope', '');
        $scope = trim($scope);

        $payload = match ($scope) {
            'appointments'  => $this->snapModel(Appointment::query()),
            'patients'      => $this->snapModel(Patient::query()),
            'doctors'       => $this->snapModel(Doctor::query()),
            'users'         => $this->snapUsersAdminStaff(),
            'user_accounts' => $this->snapUsersPublic(),
            default         => null,
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

    private function snapUsersAdminStaff(): array
    {
        $q = User::query()->whereIn('role', ['admin', 'staff']);

        $count = (int) $q->count();
        $maxUpdated = $q->max('updated_at');
        $ts = $maxUpdated ? Carbon::parse($maxUpdated)->timestamp : 0;

        return [
            'count' => $count,
            'latest_updated_at' => $maxUpdated ? Carbon::parse($maxUpdated)->toIso8601String() : null,
            'key' => $count . ':' . $ts,
        ];
    }

    private function snapUsersPublic(): array
    {
        $q = User::query()->where('role', 'user');

        $count = (int) $q->count();
        $maxUpdated = $q->max('updated_at');
        $ts = $maxUpdated ? Carbon::parse($maxUpdated)->timestamp : 0;

        return [
            'count' => $count,
            'latest_updated_at' => $maxUpdated ? Carbon::parse($maxUpdated)->toIso8601String() : null,
            'key' => $count . ':' . $ts,
        ];
    }
}
