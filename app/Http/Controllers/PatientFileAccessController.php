<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientFile;
use App\Services\PatientAccessService;
use App\Services\PatientFileStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PatientFileAccessController extends Controller
{
    public function preview(Request $request, Patient $patient, PatientFile $patientFile, PatientAccessService $access, PatientFileStorage $files)
    {
        $this->authorizeFile($request, $patient, $patientFile, $access);
        return $this->fileResponse($patientFile, $files, false);
    }

    public function download(Request $request, Patient $patient, PatientFile $patientFile, PatientAccessService $access, PatientFileStorage $files)
    {
        $this->authorizeFile($request, $patient, $patientFile, $access);
        return $this->fileResponse($patientFile, $files, true);
    }

    public function signature(Request $request, Patient $patient, string $kind, PatientAccessService $access, PatientFileStorage $files)
    {
        $user = $request->user();
        if (!$access->canViewPatient($user, $patient) || !in_array($user->role, ['staff', 'admin'], true)) {
            abort(403);
        }

        $patient->loadMissing(['informationRecord', 'informedConsent']);
        [$disk, $path] = match ($kind) {
            'information' => [$patient->informationRecord?->signature_disk, $patient->informationRecord?->signature_path],
            'consent-patient' => [$patient->informedConsent?->patient_signature_disk, $patient->informedConsent?->patient_signature_path],
            'consent-dentist' => [$patient->informedConsent?->dentist_signature_disk, $patient->informedConsent?->dentist_signature_path],
            default => abort(404),
        };

        if (!$path) abort(404);
        $path = $files->safePath($path);
        $storage = Storage::disk($files->diskName($disk));
        if (!$storage->exists($path)) abort(404);

        return $storage->response($path, $kind . '.png', [
            'Content-Type' => $storage->mimeType($path) ?: 'image/png',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    private function authorizeFile(Request $request, Patient $patient, PatientFile $patientFile, PatientAccessService $access): void
    {
        if ((int) $patientFile->patient_id !== (int) $patient->id) abort(404);
        $patientFile->setRelation('patient', $patient);
        if (!$access->canViewFile($request->user(), $patientFile)) abort(403);
    }

    private function fileResponse(PatientFile $file, PatientFileStorage $files, bool $download)
    {
        $path = $files->safePath($file->file_path);
        $storage = Storage::disk($files->diskName($file->storage_disk));
        if (!$storage->exists($path)) abort(404);

        $name = basename($file->original_name ?: ($file->title ?: basename($path)));
        $headers = [
            'Content-Type' => $file->mime ?: ($storage->mimeType($path) ?: 'application/octet-stream'),
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ];

        return $download
            ? $storage->download($path, $name, $headers)
            : $storage->response($path, $name, $headers, 'inline');
    }
}
