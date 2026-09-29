<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Models\PatientFile;
use App\Services\PatientFileStorage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\File;

class PatientFileController extends Controller
{
    private const MIME_EXTENSIONS = [
        'application/pdf' => 'pdf',
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'file' => ['required', File::types(['pdf', 'jpg', 'jpeg', 'png', 'webp'])->max(10 * 1024)],
            'patient_visible' => ['nullable', 'boolean'],
        ]);

        $upload = $request->file('file');
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = (string) $finfo->file($upload->getRealPath());
        if (!isset(self::MIME_EXTENSIONS[$mime])) {
            return back()->withErrors(['file' => 'Only genuine PDF, JPEG, PNG, or WebP files are allowed.']);
        }

        $path = 'documents/' . $patient->id . '/' . Str::uuid() . '.' . self::MIME_EXTENSIONS[$mime];
        $disk = Storage::disk(PatientFileStorage::PRIVATE_DISK);
        $contents = file_get_contents($upload->getRealPath());
        if ($contents === false) {
            return back()->withErrors(['file' => 'The uploaded file could not be read securely.']);
        }
        $disk->put($path, $contents);
        if (!$disk->exists($path)) {
            return back()->withErrors(['file' => 'The file could not be stored securely.']);
        }

        try {
            PatientFile::create([
                'patient_id' => $patient->id,
                'title' => $validated['title'],
                'original_name' => Str::limit(basename($upload->getClientOriginalName()), 255, ''),
                'file_path' => $path,
                'storage_disk' => PatientFileStorage::PRIVATE_DISK,
                'mime' => $mime,
                'size' => $upload->getSize(),
                'patient_visible' => $request->boolean('patient_visible'),
            ]);
        } catch (\Throwable $exception) {
            $disk->delete($path);
            throw $exception;
        }

        return back()->with('success', 'Patient file uploaded securely.');
    }
}
