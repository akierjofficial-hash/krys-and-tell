<?php

namespace App\Services;

use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\User;

class PatientAccessService
{
    public function linkedPatientIds(?User $user): array
    {
        if (!$user || !$user->is_active || $user->role !== 'user') {
            return [];
        }

        return $user->verifiedPatients()
            ->pluck('patients.id')
            ->map(fn ($id) => (int) $id)
            ->all();
    }

    public function canViewPatient(?User $user, Patient $patient): bool
    {
        if (!$user || !$user->is_active) return false;
        if (in_array($user->role, ['staff', 'admin'], true)) return true;
        if ($user->role !== 'user') return false;

        return $user->verifiedPatients()->whereKey($patient->getKey())->exists();
    }

    public function canViewFile(?User $user, PatientFile $file): bool
    {
        if (!$user || !$user->is_active) return false;
        if (in_array($user->role, ['staff', 'admin'], true)) return true;

        return $user->role === 'user'
            && $file->patient_visible
            && $this->canViewPatient($user, $file->patient);
    }
}
