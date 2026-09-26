<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\InstallmentPayment;
use App\Models\InstallmentPlan;
use App\Models\Patient;
use App\Models\Payment;
use App\Models\Service;
use App\Models\User;
use App\Models\Visit;
use App\Services\FinancialService;
use App\Services\AdminAccountService;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPhaseOneTest extends TestCase
{
    use RefreshDatabase;

    private function admin(array $attributes = []): User
    {
        return User::factory()->create(array_merge(['role' => 'admin', 'is_active' => true, 'password' => Hash::make('password')], $attributes));
    }

    private function staff(array $attributes = []): User
    {
        return User::factory()->create(array_merge(['role' => 'staff', 'is_active' => true], $attributes));
    }

    private function updatePayload(User $user, array $overrides = []): array
    {
        return array_merge(['name' => $user->name, 'email' => $user->email, 'role' => $user->role, 'is_active' => '1'], $overrides);
    }

    public function test_admin_cannot_demote_themselves(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.users.update', $admin), $this->updatePayload($admin, ['role' => 'staff', 'current_password' => 'password', 'admin_reason' => 'test']))->assertSessionHasErrors('role');
        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_admin_cannot_deactivate_themselves_through_edit(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->put(route('admin.users.update', $admin), $this->updatePayload($admin, ['is_active' => null]))->assertSessionHasErrors('role');
        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_cannot_deactivate_themselves_through_toggle(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('admin.users.toggleActive', $admin))->assertSessionHasErrors('account');
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->delete(route('admin.users.destroy', $admin), ['current_password' => 'password', 'admin_reason' => 'test'])->assertSessionHasErrors('account');
        $this->assertNotSoftDeleted($admin);
    }

    public function test_last_active_admin_cannot_be_demoted(): void
    {
        $actor = $this->admin();
        $authorizer = $this->admin(['is_active' => false]);
        $this->expectException(ValidationException::class);
        app(AdminAccountService::class)->update($authorizer, $actor, $this->updatePayload($actor, ['role' => 'staff', 'current_password' => 'password', 'admin_reason' => 'test']));
    }

    public function test_last_active_admin_cannot_be_deactivated(): void
    {
        $actor = $this->admin();
        $inactiveAdmin = $this->admin(['is_active' => false]);
        $this->expectException(ValidationException::class);
        app(AdminAccountService::class)->toggle($inactiveAdmin, $actor, ['current_password' => 'password', 'admin_reason' => 'test']);
    }

    public function test_last_active_admin_cannot_be_deleted(): void
    {
        $actor = $this->admin();
        $inactiveAdmin = $this->admin(['is_active' => false]);
        $this->expectException(ValidationException::class);
        app(AdminAccountService::class)->delete($inactiveAdmin, $actor, ['current_password' => 'password', 'admin_reason' => 'test']);
    }

    public function test_another_admin_can_be_changed_when_one_active_admin_will_remain(): void
    {
        $actor = $this->admin();
        $target = $this->admin();
        $this->actingAs($actor)->put(route('admin.users.update', $target), $this->updatePayload($target, ['role' => 'staff', 'current_password' => 'password', 'admin_reason' => 'Role no longer needed']))->assertSessionHasNoErrors();
        $this->assertSame('staff', $target->fresh()->role);
    }

    public function test_deactivated_active_sessions_lose_protected_access(): void
    {
        $admin = $this->admin(['is_active' => false]);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_sensitive_account_changes_require_current_admin_password(): void
    {
        $actor = $this->admin(); $target = $this->staff();
        $this->actingAs($actor)->put(route('admin.users.update', $target), $this->updatePayload($target, ['role' => 'admin', 'admin_reason' => 'Promotion']))->assertSessionHasErrors('current_password');
    }

    public function test_deleted_accounts_can_be_listed_and_restored(): void
    {
        $admin = $this->admin(); $staff = $this->staff(); $staff->delete();
        $this->actingAs($admin)->get(route('admin.deleted_accounts.index'))->assertOk()->assertSee($staff->email);
        $this->post(route('admin.users.restore', $staff->id))->assertRedirect();
        $this->assertNotSoftDeleted($staff);
    }

    public function test_collected_amount_does_not_double_count_downpayments(): void
    {
        [$patient, $visit] = $this->patientVisit();
        $plan = InstallmentPlan::create(['patient_id'=>$patient->id,'visit_id'=>$visit->id,'total_cost'=>40000,'downpayment'=>8000,'balance'=>32000,'months'=>4,'start_date'=>today(),'status'=>'Partially Paid']);
        InstallmentPayment::create(['installment_plan_id'=>$plan->id,'visit_id'=>$visit->id,'patient_id'=>$patient->id,'amount'=>8000,'payment_date'=>today(),'month_number'=>0,'payment_method'=>'Cash']);
        $this->assertSame(8000.0, app(FinancialService::class)->collectedOn(today()));
    }

    public function test_soft_deleted_financial_records_are_excluded(): void
    {
        [$patient, $visit] = $this->patientVisit();
        $payment = Payment::create(['visit_id'=>$visit->id,'amount'=>500,'payment_date'=>today(),'payment_method'=>'Cash']);
        $payment->delete();
        $this->assertSame(0.0, app(FinancialService::class)->collectedOn(today()));
    }

    public function test_soft_deleted_appointments_patients_and_visits_are_excluded_from_reports(): void
    {
        $admin = $this->admin(); [$patient, $visit] = $this->patientVisit(); $patient->delete(); $visit->delete();
        $appointment = $this->pendingAppointment(['patient_id' => null]); $appointment->delete();
        $response = $this->actingAs($admin)->get(route('admin.analytics.index'));
        $response->assertOk()->assertViewHas('kpiAppointments', 0)->assertViewHas('kpiNewPatients', 0);
    }

    public function test_approval_rejects_non_pending_and_repeated_requests(): void
    {
        $admin = $this->admin(); $appointment = $this->pendingAppointment();
        $appointment->update(['status' => 'upcoming']);
        $this->actingAs($admin)->post(route('admin.approvals.approve', $appointment))->assertSessionHas('error', 'This booking request has already been processed.');
        $this->assertSame('upcoming', $appointment->fresh()->status);
    }

    public function test_approval_does_not_match_patients_by_name_only(): void
    {
        $admin = $this->admin();
        $existing = Patient::create(['first_name'=>'Same','last_name'=>'Name','gender'=>null,'birthdate'=>null,'email'=>'old@example.test']);
        $appointment = $this->pendingAppointment(['public_first_name'=>'Same','public_last_name'=>'Name','public_email'=>'new@example.test']);
        $this->actingAs($admin)->post(route('admin.approvals.approve', $appointment))->assertSessionHasNoErrors();
        $this->assertNotSame($existing->id, $appointment->fresh()->patient_id);
    }

    public function test_approval_never_inserts_fake_birthdate(): void
    {
        $admin = $this->admin(); $appointment = $this->pendingAppointment();
        $this->actingAs($admin)->post(route('admin.approvals.approve', $appointment))->assertSessionHasNoErrors();
        $this->assertNull($appointment->fresh()->patient->birthdate);
    }

    public function test_decline_requires_a_reason(): void
    {
        $admin = $this->admin(); $appointment = $this->pendingAppointment();
        $this->actingAs($admin)->post(route('admin.approvals.decline', $appointment))->assertSessionHas('error');
        $this->assertSame('pending', $appointment->fresh()->status);
    }

    public function test_admin_patient_page_is_read_only(): void
    {
        $admin = $this->admin(); [$patient] = $this->patientVisit();
        $this->actingAs($admin)->get(route('admin.patients.show', $patient))->assertOk()->assertSee('Read-only')->assertDontSee('Save Changes');
        $this->assertFalse(app('router')->has('admin.patients.edit'));
    }

    public function test_staff_approval_workflow_still_works(): void
    {
        $staff = $this->staff(); $appointment = $this->pendingAppointment();
        $this->actingAs($staff)->post(route('staff.approvals.approve', $appointment))->assertSessionHasNoErrors();
        $this->assertContains($appointment->fresh()->status, ['upcoming', 'walked_in']);
    }

    public function test_non_admin_users_cannot_access_admin_routes(): void
    {
        $this->actingAs($this->staff())->get(route('admin.dashboard'))->assertForbidden();
    }

    public function test_sensitive_logs_exclude_passwords(): void
    {
        $actor = $this->admin(); $target = $this->staff();
        $this->actingAs($actor)->put(route('admin.users.update', $target), $this->updatePayload($target, ['password'=>'new-password','current_password'=>'password','admin_reason'=>'Reset requested']));
        $log = ActivityLog::where('event', 'account.updated')->firstOrFail();
        $encoded = json_encode($log->toArray());
        $this->assertStringNotContainsString('new-password', $encoded);
        $this->assertStringNotContainsString('password', json_encode($log->before_values));
    }

    public function test_existing_activity_logs_remain_readable(): void
    {
        $admin = $this->admin();
        ActivityLog::create(['user_id'=>$admin->id,'event'=>'update','description'=>null,'route_name'=>'legacy.route','url'=>'/legacy','method'=>'POST','created_at'=>now()]);
        $this->actingAs($admin)->get(route('admin.activity.index'))->assertOk()->assertSee('Update');
    }

    public function test_admin_navigation_uses_oversight_groups_and_has_no_header_decision_forms(): void
    {
        $this->actingAs($this->admin())->get(route('admin.dashboard'))->assertOk()
            ->assertSee('Accounts &amp; Security', false)->assertSee('Staff &amp; Admin Accounts', false)->assertSee('Booking Requests')
            ->assertDontSee('data-action="approve"', false);
    }

    public function test_dashboard_uses_authoritative_collection_total(): void
    {
        $admin = $this->admin(); [$patient, $visit] = $this->patientVisit();
        Payment::create(['visit_id'=>$visit->id,'amount'=>1250,'payment_date'=>today(),'payment_method'=>'Cash']);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertSee('₱1,250.00');
    }

    private function patientVisit(): array
    {
        $patient = Patient::create(['first_name'=>'Test','last_name'=>'Patient','gender'=>null,'birthdate'=>null]);
        $visit = Visit::create(['patient_id'=>$patient->id,'visit_date'=>today(),'status'=>'partial','price'=>2000]);
        return [$patient, $visit];
    }

    private function pendingAppointment(array $overrides = []): Appointment
    {
        $service = Service::create(['name'=>'Walk-in review '.uniqid(),'base_price'=>500,'duration_minutes'=>1]);
        $appointment = new Appointment();
        $appointment->forceFill(array_merge([
            'service_id'=>$service->id,'patient_id'=>null,'appointment_date'=>today()->addDay()->toDateString(),
            'appointment_time'=>null,'status'=>'pending','is_walk_in_request'=>true,'public_first_name'=>'New',
            'public_last_name'=>'Patient','public_email'=>uniqid().'@example.test','public_gender'=>null,'public_birthdate'=>null,
        ], $overrides));
        $appointment->save();
        return $appointment;
    }
}
