<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PatientFileStorage
{
    public const PRIVATE_DISK = 'patient_private';

    public function safePath(string $path): string
    {
        $path = str_replace('\\', '/', trim($path));
        $segments = explode('/', $path);

        if ($path === '' || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:/', $path)
            || str_contains($path, "\0") || in_array('..', $segments, true)) {
            abort(404);
        }

        return ltrim($path, '/');
    }

    public function diskName(?string $disk): string
    {
        return $disk === self::PRIVATE_DISK ? self::PRIVATE_DISK : 'public';
    }

    public function dataUri(?string $disk, ?string $path): ?string
    {
        if (!$path) return null;

        $path = $this->safePath($path);
        $storage = Storage::disk($this->diskName($disk));
        if (!$storage->exists($path)) return null;

        $mime = $storage->mimeType($path) ?: 'application/octet-stream';
        return 'data:' . $mime . ';base64,' . base64_encode($storage->get($path));
    }

    public function storeSignature(?string $dataUrl, string $folder): ?array
    {
        if ($dataUrl === null || trim($dataUrl) === '') return null;
        if (!preg_match('/^data:image\/png;base64,([A-Za-z0-9+\/=\r\n]+)$/', $dataUrl, $matches)) {
            throw ValidationException::withMessages(['signature' => 'The signature must be a valid PNG image.']);
        }

        $raw = base64_decode(preg_replace('/\s+/', '', $matches[1]), true);
        if ($raw === false || strlen($raw) > 600000) {
            throw ValidationException::withMessages(['signature' => 'The signature image is invalid or exceeds 600 KB.']);
        }

        $image = @getimagesizefromstring($raw);
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        if (!$image || ($image['mime'] ?? null) !== 'image/png' || $finfo->buffer($raw) !== 'image/png') {
            throw ValidationException::withMessages(['signature' => 'The signature contents are not a valid PNG image.']);
        }
        if (($image[0] ?? 0) > 2400 || ($image[1] ?? 0) > 1200) {
            throw ValidationException::withMessages(['signature' => 'The signature image dimensions are too large.']);
        }

        $path = trim($folder, '/') . '/' . Str::uuid() . '.png';
        Storage::disk(self::PRIVATE_DISK)->put($path, $raw);
        if (!Storage::disk(self::PRIVATE_DISK)->exists($path)) {
            throw ValidationException::withMessages(['signature' => 'The signature could not be stored securely.']);
        }

        return ['path' => $path, 'disk' => self::PRIVATE_DISK];
    }
}
