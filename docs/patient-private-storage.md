# Patient private storage rollout

Patient documents and signatures use the `patient_private` Laravel disk. The disk has no public URL and is served only by authenticated controller routes after role, patient, and file ownership checks.

## Render storage

Render's application filesystem is ephemeral. Before migrating production files:

1. Create or attach a Render persistent disk to the web service.
2. Mount it at `/var/data` (or another private path outside the public web root).
3. Set `PATIENT_PRIVATE_ROOT=/var/data/krys-and-tell/patients` on every service instance that reads or writes patient files. Do not point it inside `public/` or `storage/app/public/`.
4. Deploy the schema migrations before accepting new patient uploads.
5. Back up the database and `storage/app/public` files before moving existing data.

## Existing file migration

Run the inspection first:

```shell
php artisan patient-files:migrate-private --dry-run
```

Then run the migration:

```shell
php artisan patient-files:migrate-private
```

For each database reference, the command copies the public file to private storage, compares its byte count and SHA-256 hash, updates the disk reference, and only then deletes the public copy. A missing source, failed write, hash mismatch, unexpected disk, or concurrent database change is reported as a failure and leaves the public source and database reference unchanged.

The command is idempotent. Running it again verifies private records and removes a stale public copy only when the two copies match exactly. A nonzero exit code means at least one item needs investigation; do not manually remove its public source.

After a successful run, verify staff previews/downloads, PDF signature rendering, and a deliberately shared patient document. Old `/storage/...` URLs should stop resolving because their public copies no longer exist.
