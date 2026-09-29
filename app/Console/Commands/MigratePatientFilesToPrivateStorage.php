<?php

namespace App\Console\Commands;

use App\Services\PatientFileStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MigratePatientFilesToPrivateStorage extends Command
{
    protected $signature = 'patient-files:migrate-private {--dry-run : Inspect and verify without copying, updating, or deleting}';
    protected $description = 'Safely move legacy patient documents and signatures from public storage to private storage';

    private int $migrated = 0;
    private int $cleaned = 0;
    private int $skipped = 0;
    private int $failed = 0;

    public function handle(PatientFileStorage $files): int
    {
        $items = $this->items();
        $this->info(($this->option('dry-run') ? 'DRY RUN: ' : '') . $items->count() . ' sensitive file reference(s) found.');

        foreach ($items as $item) {
            $this->migrateItem($item, $files);
        }

        $this->newLine();
        $this->line("Migrated: {$this->migrated}; stale public copies removed: {$this->cleaned}; skipped: {$this->skipped}; failed: {$this->failed}.");

        return $this->failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    private function items()
    {
        $items = collect();

        DB::table('patient_files')->whereNotNull('file_path')->orderBy('id')->each(function ($row) use ($items) {
            $items->push($this->item('patient_files', $row->id, 'file_path', 'storage_disk', $row->file_path, $row->storage_disk ?? 'public'));
        });
        DB::table('patient_information_records')->whereNotNull('signature_path')->orderBy('id')->each(function ($row) use ($items) {
            $items->push($this->item('patient_information_records', $row->id, 'signature_path', 'signature_disk', $row->signature_path, $row->signature_disk ?? 'public'));
        });
        DB::table('patient_informed_consents')->whereNotNull('patient_signature_path')->orderBy('id')->each(function ($row) use ($items) {
            $items->push($this->item('patient_informed_consents', $row->id, 'patient_signature_path', 'patient_signature_disk', $row->patient_signature_path, $row->patient_signature_disk ?? 'public'));
        });
        DB::table('patient_informed_consents')->whereNotNull('dentist_signature_path')->orderBy('id')->each(function ($row) use ($items) {
            $items->push($this->item('patient_informed_consents', $row->id, 'dentist_signature_path', 'dentist_signature_disk', $row->dentist_signature_path, $row->dentist_signature_disk ?? 'public'));
        });

        return $items;
    }

    private function item(string $table, int $id, string $pathColumn, string $diskColumn, string $path, string $disk): object
    {
        return (object) compact('table', 'id', 'pathColumn', 'diskColumn', 'path', 'disk');
    }

    private function migrateItem(object $item, PatientFileStorage $files): void
    {
        $label = "{$item->table}#{$item->id}:{$item->pathColumn}";
        try {
            $path = $files->safePath($item->path);
            $public = Storage::disk('public');
            $private = Storage::disk(PatientFileStorage::PRIVATE_DISK);

            if ($item->disk === PatientFileStorage::PRIVATE_DISK) {
                if (!$private->exists($path)) {
                    throw new \RuntimeException('record points to private storage, but the private file is missing');
                }
                if ($public->exists($path)) {
                    if (!$this->sameFile($public, $private, $path)) {
                        throw new \RuntimeException('public and private copies differ; public copy was retained');
                    }
                    if (!$this->option('dry-run')) {
                        $public->delete($path);
                        if ($public->exists($path)) throw new \RuntimeException('verified public copy could not be removed');
                    }
                    $this->cleaned++;
                    $this->line("CLEAN {$label}");
                } else {
                    $this->skipped++;
                }
                return;
            }

            if ($item->disk !== 'public') {
                throw new \RuntimeException("unsupported source disk '{$item->disk}'");
            }
            if (!$public->exists($path)) {
                throw new \RuntimeException('public source file is missing; database reference was not changed');
            }

            if ($this->option('dry-run')) {
                $this->line("WOULD MIGRATE {$label}");
                $this->migrated++;
                return;
            }

            if (!$private->exists($path)) {
                $stream = $public->readStream($path);
                if (!is_resource($stream)) throw new \RuntimeException('could not read public source');
                try {
                    $private->writeStream($path, $stream);
                } finally {
                    fclose($stream);
                }
            }

            if (!$private->exists($path) || !$this->sameFile($public, $private, $path)) {
                throw new \RuntimeException('private copy verification failed; public source was retained');
            }

            $updated = DB::table($item->table)->where('id', $item->id)->where($item->pathColumn, $item->path)
                ->update([$item->diskColumn => PatientFileStorage::PRIVATE_DISK, 'updated_at' => now()]);
            if ($updated !== 1) throw new \RuntimeException('database reference changed concurrently; public source was retained');

            $public->delete($path);
            if ($public->exists($path)) throw new \RuntimeException('database now uses private storage, but the public copy could not be removed');

            $this->migrated++;
            $this->line("MIGRATED {$label}");
        } catch (\Throwable $e) {
            $this->failed++;
            $this->error("FAILED {$label}: {$e->getMessage()}");
        }
    }

    private function sameFile($source, $destination, string $path): bool
    {
        if ($source->size($path) !== $destination->size($path)) return false;
        return hash_equals($this->hash($source, $path), $this->hash($destination, $path));
    }

    private function hash($disk, string $path): string
    {
        $stream = $disk->readStream($path);
        if (!is_resource($stream)) throw new \RuntimeException('could not open file for verification');
        $context = hash_init('sha256');
        try {
            hash_update_stream($context, $stream);
            return hash_final($context);
        } finally {
            fclose($stream);
        }
    }
}
