<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\PatientInformationRecord;
use App\Models\PatientUserLink;
use App\Models\Service;
use App\Models\User;
use App\Services\PatientFileStorage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class PatientPrivacyAndLinkingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake(PatientFileStorage::PRIVATE_DISK);
    }

    public function test_patient_files_require_authentication_and_server_side_patient_authorization(): void
    {
        $patient = $this->patient('Allowed');
        $otherPatient = $this->patient('Other');
        $file = $this->privateFile($patient, true);

        $this->get(route('patient-files.preview', [$patient, $file]))
            ->assertRedirect(route('login'));

        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $response = $this->actingAs($staff)
            ->get(route('patient-files.preview', [$patient, $file]))
            ->assertOk();
        $this->assertStringContainsString('private', (string) $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('no-store', (string) $response->headers->get('Cache-Control'));

        $this->actingAs($staff)
            ->get(route('patient-files.preview', [$otherPatient, $file]))
            ->assertNotFound();

        $admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->actingAs($admin)
            ->get(route('patient-files.download', [$patient, $file]))
            ->assertOk();
    }

    public function test_verified_patient_can_only_open_explicitly_shared_files_for_linked_patients(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $linkedPatient = $this->patient('Linked');
        $otherPatient = $this->patient('Unlinked');
        $this->link($user, $linkedPatient, $staff);

        $shared = $this->privateFile($linkedPatient, true, 'shared.pdf');
        $private = $this->privateFile($linkedPatient, false, 'staff-only.pdf');
        $other = $this->privateFile($otherPatient, true, 'other.pdf');

        $this->actingAs($user)->get(route('patient-files.download', [$linkedPatient, $shared]))->assertOk();
        $this->actingAs($user)->get(route('patient-files.download', [$linkedPatient, $private]))->assertForbidden();
        $this->actingAs($user)->get(route('patient-files.download', [$otherPatient, $other]))->assertForbidden();
    }

    public function test_path_traversal_references_are_rejected_even_for_staff(): void
    {
        $patient = $this->patient('Traversal');
        $file = PatientFile::create([
            'patient_id' => $patient->id,
            'title' => 'Unsafe reference',
            'file_path' => '../.env',
            'storage_disk' => PatientFileStorage::PRIVATE_DISK,
            'mime' => 'text/plain',
            'patient_visible' => false,
        ]);
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);

        $this->actingAs($staff)
            ->get(route('patient-files.preview', [$patient, $file]))
            ->assertNotFound();
    }

    public function test_signatures_are_private_to_clinic_roles_even_when_a_patient_record_is_linked(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $patient = $this->patient('Signature');
        $this->link($user, $patient, $staff);
        $path = 'signatures/patient-info/signature.png';
        Storage::disk(PatientFileStorage::PRIVATE_DISK)->put($path, base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNk+A8AAQUBAScY42YAAAAASUVORK5CYII='
        ));
        PatientInformationRecord::create([
            'patient_id' => $patient->id,
            'signature_path' => $path,
            'signature_disk' => PatientFileStorage::PRIVATE_DISK,
        ]);

        $this->actingAs($staff)
            ->get(route('patient-signatures.show', [$patient, 'information']))
            ->assertOk();
        $this->actingAs($user)
            ->get(route('patient-signatures.show', [$patient, 'information']))
            ->assertForbidden();
    }

    public function test_patient_uploads_validate_contents_and_use_generated_private_names(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $patient = $this->patient('Upload');

        $this->actingAs($staff)->post(route('staff.patients.files.store', $patient), [
            'title' => 'Scanned consent',
            'file' => UploadedFile::fake()->createWithContent('predictable-name.pdf', "%PDF-1.4\n1 0 obj\n<<>>\nendobj\n%%EOF"),
            'patient_visible' => '1',
        ])->assertRedirect();

        $record = PatientFile::firstOrFail();
        $this->assertSame(PatientFileStorage::PRIVATE_DISK, $record->storage_disk);
        $this->assertSame('predictable-name.pdf', $record->original_name);
        $this->assertNotSame('predictable-name.pdf', basename($record->file_path));
        $this->assertTrue($record->patient_visible);
        Storage::disk(PatientFileStorage::PRIVATE_DISK)->assertExists($record->file_path);

        $this->actingAs($staff)->from(route('staff.patients.show', $patient))->post(route('staff.patients.files.store', $patient), [
            'title' => 'Disguised executable',
            'file' => UploadedFile::fake()->createWithContent('malware.pdf', "MZ\x90\x00\x03\x00\x00\x00not-a-pdf"),
        ])->assertSessionHasErrors('file');
        $this->assertDatabaseCount('patient_files', 1);
    }

    public function test_inactive_accounts_lose_private_file_access(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'user', 'is_active' => false]);
        $patient = $this->patient('Inactive');
        $this->link($user, $patient, $staff);
        $file = $this->privateFile($patient, true);

        $this->actingAs($user)
            ->get(route('patient-files.preview', [$patient, $file]))
            ->assertRedirect(route('userlogin'));
        $this->assertGuest();
    }

    public function test_staff_linking_is_explicit_audited_and_unlinking_revokes_access(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $patient = $this->patient('Child', 'family@example.test', '09170000000');
        $file = $this->privateFile($patient, true);

        $this->actingAs($staff)->post(route('staff.patients.account-links.store', $patient), [
            'user_id' => $user->id,
            'relationship' => 'guardian',
            'verification_note' => 'Identity checked against clinic registration form.',
            'confirm_identity' => '1',
        ])->assertRedirect(route('staff.patients.account-links.index', $patient));

        $link = PatientUserLink::firstOrFail();
        $this->assertSame($staff->id, $link->verified_by_user_id);
        $this->assertNotNull($link->verified_at);
        $this->actingAs($user)->get(route('patient-files.preview', [$patient, $file]))->assertOk();

        $this->actingAs($staff)->delete(route('staff.patients.account-links.destroy', [$patient, $link]), [
            'unlink_reason' => 'Guardian access was withdrawn by the clinic.',
            'confirm_unlink' => '1',
        ])->assertRedirect();

        $link->refresh();
        $this->assertSame($staff->id, $link->unlinked_by_user_id);
        $this->assertNotNull($link->unlinked_at);
        $this->actingAs($user)->get(route('patient-files.preview', [$patient, $file]))->assertForbidden();
    }

    public function test_shared_contact_details_never_link_records_and_guardian_can_have_multiple_verified_links(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $guardian = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'email' => 'family@example.test',
        ]);
        $childOne = $this->patient('FirstChild', 'family@example.test', '09170000000');
        $childTwo = $this->patient('SecondChild', 'family@example.test', '09170000000');
        $unrelated = $this->patient('SameContactButUnlinked', 'family@example.test', '09170000000');

        $this->plan($childOne, 11001);
        $this->plan($childTwo, 22002);
        $unrelatedPlan = $this->plan($unrelated, 99009);

        $this->link($guardian, $childOne, $staff, 'guardian');
        $this->link($guardian, $childTwo, $staff, 'guardian');

        $this->actingAs($guardian)->get(route('public.installments.index'))
            ->assertOk()
            ->assertSee('FirstChild')
            ->assertSee('SecondChild')
            ->assertDontSee('SameContactButUnlinked');
        $this->actingAs($guardian)->get(route('public.installments.show', $unrelatedPlan))->assertForbidden();
    }

    public function test_unverified_account_sees_connection_state_and_no_email_matched_appointments(): void
    {
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'email' => 'match@example.test',
        ]);
        $patient = $this->patient('EmailMatchOnly', 'match@example.test');
        $hiddenService = Service::create(['name' => 'Hidden Email Match Treatment', 'base_price' => 100]);
        $ownedService = Service::create(['name' => 'Owned Online Booking', 'base_price' => 100]);

        Appointment::forceCreate([
            'patient_id' => $patient->id,
            'service_id' => $hiddenService->id,
            'appointment_date' => now()->addDays(5)->toDateString(),
            'appointment_time' => '10:00:00',
            'status' => 'approved',
            'public_email' => $user->email,
        ]);
        Appointment::forceCreate([
            'user_id' => $user->id,
            'patient_id' => null,
            'service_id' => $ownedService->id,
            'appointment_date' => now()->addDays(6)->toDateString(),
            'appointment_time' => '11:00:00',
            'status' => 'approved',
            'public_email' => $user->email,
        ]);

        $this->actingAs($user)->get(route('profile.show'))
            ->assertOk()
            ->assertSee('Owned Online Booking')
            ->assertDontSee('Hidden Email Match Treatment')
            ->assertSee('Not connected');
        $this->actingAs($user)->get(route('public.installments.index'))
            ->assertOk()
            ->assertSee('Your clinic record is not connected yet.');
    }

    public function test_legacy_email_inferred_appointment_ownership_is_removed_without_touching_real_online_bookings(): void
    {
        $user = User::factory()->create(['role' => 'user', 'is_active' => true]);
        $patient = $this->patient('LegacyAppointment', $user->email);
        $service = Service::create(['name' => 'Migration Test', 'base_price' => 100]);
        $base = [
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->toDateString(),
            'appointment_time' => '09:00:00',
            'status' => 'approved',
            'public_email' => $user->email,
        ];
        $inferred = Appointment::forceCreate($base);
        $online = Appointment::forceCreate(array_merge($base, [
            'appointment_time' => '10:00:00',
            'public_name' => 'Real Online Booking',
            'public_first_name' => 'Real',
            'public_last_name' => 'Booking',
        ]));

        $migration = require database_path('migrations/2026_09_28_000003_remove_inferred_appointment_account_links.php');
        $migration->up();

        $this->assertNull($inferred->fresh()->user_id);
        $this->assertSame($user->id, $online->fresh()->user_id);
    }

    public function test_account_email_change_does_not_transfer_patient_access(): void
    {
        $staff = User::factory()->create(['role' => 'staff', 'is_active' => true]);
        $user = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'email' => 'before@example.test',
            'email_verified_at' => now(),
        ]);
        $linked = $this->patient('StillLinked', 'before@example.test');
        $sameNewEmail = $this->patient('MustRemainPrivate', 'after@example.test');
        $this->link($user, $linked, $staff);
        $linkedFile = $this->privateFile($linked, true, 'linked.pdf');
        $otherFile = $this->privateFile($sameNewEmail, true, 'other.pdf');

        $this->actingAs($user)->put(route('user.profile.update'), [
            'name' => $user->name,
            'email' => 'after@example.test',
            'notify_24h' => '0',
            'notify_1h' => '0',
        ])->assertRedirect();

        $user->refresh();
        $this->assertSame('after@example.test', $user->email);
        $this->assertNull($user->email_verified_at);
        $this->actingAs($user)->get(route('patient-files.preview', [$linked, $linkedFile]))->assertOk();
        $this->actingAs($user)->get(route('patient-files.preview', [$sameNewEmail, $otherFile]))->assertForbidden();
    }

    public function test_google_sign_in_cannot_take_over_an_account_connected_to_another_google_identity(): void
    {
        $account = User::factory()->create([
            'role' => 'user',
            'is_active' => true,
            'email' => 'google-owner@example.test',
            'google_id' => 'google-id-original',
        ]);
        $googleUser = (new SocialiteUser())->map([
            'id' => 'google-id-attacker',
            'name' => 'Different Google Identity',
            'email' => $account->email,
        ])->setRaw(['email_verified' => true]);
        $provider = Mockery::mock();
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->once()->with('google')->andReturn($provider);

        $this->get(route('google.callback'))
            ->assertRedirect(route('userlogin'))
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertSame('google-id-original', $account->fresh()->google_id);
    }

    public function test_legacy_public_files_are_verified_migrated_and_public_copy_is_removed(): void
    {
        $patient = $this->patient('Legacy');
        $path = 'patient-files/legacy-document.pdf';
        Storage::disk('public')->put($path, "%PDF-1.4\nlegacy");
        $file = PatientFile::create([
            'patient_id' => $patient->id,
            'title' => 'Legacy document',
            'file_path' => $path,
            'storage_disk' => 'public',
            'mime' => 'application/pdf',
            'patient_visible' => false,
        ]);

        $this->artisan('patient-files:migrate-private')->assertSuccessful();

        $this->assertSame(PatientFileStorage::PRIVATE_DISK, $file->fresh()->storage_disk);
        Storage::disk(PatientFileStorage::PRIVATE_DISK)->assertExists($path);
        Storage::disk('public')->assertMissing($path);
        $this->get('/storage/' . $path)->assertStatus(403);
    }

    public function test_missing_legacy_source_is_never_deleted_or_repointed(): void
    {
        $patient = $this->patient('Missing');
        $file = PatientFile::create([
            'patient_id' => $patient->id,
            'title' => 'Missing legacy document',
            'file_path' => 'patient-files/missing.pdf',
            'storage_disk' => 'public',
            'mime' => 'application/pdf',
            'patient_visible' => false,
        ]);

        $this->artisan('patient-files:migrate-private')->assertFailed();
        $this->assertSame('public', $file->fresh()->storage_disk);
        Storage::disk(PatientFileStorage::PRIVATE_DISK)->assertMissing($file->file_path);
    }

    private function patient(string $firstName, ?string $email = null, ?string $phone = null): Patient
    {
        return Patient::create([
            'first_name' => $firstName,
            'last_name' => 'PrivacyTest',
            'gender' => 'Other',
            'birthdate' => '2010-01-01',
            'email' => $email,
            'contact_number' => $phone,
        ]);
    }

    private function privateFile(Patient $patient, bool $patientVisible, string $name = 'record.pdf'): PatientFile
    {
        $path = 'documents/' . $patient->id . '/' . $name;
        Storage::disk(PatientFileStorage::PRIVATE_DISK)->put($path, "%PDF-1.4\nprivate");

        return PatientFile::create([
            'patient_id' => $patient->id,
            'title' => $name,
            'original_name' => $name,
            'file_path' => $path,
            'storage_disk' => PatientFileStorage::PRIVATE_DISK,
            'mime' => 'application/pdf',
            'size' => 16,
            'patient_visible' => $patientVisible,
        ]);
    }

    private function link(User $user, Patient $patient, User $staff, string $relationship = 'self'): PatientUserLink
    {
        return PatientUserLink::create([
            'patient_id' => $patient->id,
            'user_id' => $user->id,
            'relationship' => $relationship,
            'verification_note' => 'Verified in test.',
            'verified_at' => now(),
            'verified_by_user_id' => $staff->id,
        ]);
    }

    private function plan(Patient $patient, float $balance): InstallmentPlan
    {
        return InstallmentPlan::create([
            'patient_id' => $patient->id,
            'total_cost' => $balance,
            'downpayment' => 0,
            'balance' => $balance,
            'months' => 12,
            'start_date' => now()->toDateString(),
            'status' => 'Partially Paid',
        ]);
    }
}
