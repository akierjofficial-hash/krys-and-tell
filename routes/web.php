<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\GoogleController;

// ✅ User controllers
use App\Http\Controllers\User\UserProfileController;
use App\Http\Controllers\Auth\UserLoginController;

// Staff controllers
use App\Http\Controllers\Staff\DashboardController;
use App\Http\Controllers\Staff\PatientController;
use App\Http\Controllers\Staff\VisitController;
use App\Http\Controllers\Staff\PaymentController;
use App\Http\Controllers\Staff\AppointmentController;
use App\Http\Controllers\Staff\ServiceController;
use App\Http\Controllers\Staff\InstallmentPlanController;
use App\Http\Controllers\Staff\InstallmentPaymentController;
use App\Http\Controllers\Staff\PatientImportExportController;
use App\Http\Controllers\Staff\ApprovalRequestController;
use App\Http\Controllers\Staff\VisitImportExportController;
use App\Http\Controllers\Staff\InstallmentImportExportController;
use App\Http\Controllers\Staff\LiveSnapshotController as StaffLiveSnapshotController;

// ✅ Contact inbox controllers
use App\Http\Controllers\Public\ContactMessageController as PublicContactMessageController;
use App\Http\Controllers\Staff\ContactMessageController as StaffContactMessageController;

// Admin controllers
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminScheduleController;
use App\Http\Controllers\Admin\AdminAppointmentController;
use App\Http\Controllers\Admin\AdminPatientController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminDoctorController;
use App\Http\Controllers\Admin\AdminUserAccountsController;
use App\Http\Controllers\Admin\AdminApprovalRequestController;
use App\Http\Controllers\Admin\LiveSnapshotController as AdminLiveSnapshotController;

// Public controllers
use App\Http\Controllers\Public\PublicServiceController;
use App\Http\Controllers\Public\PublicBookingController;
use App\Http\Controllers\Public\MessengerBookingController; // ✅ ADD THIS
use App\Http\Controllers\Public\PublicInstallmentController;
use App\Http\Controllers\PushSubscriptionController;

/*
|--------------------------------------------------------------------------
| GOOGLE AUTH ROUTES (PUBLIC)
|--------------------------------------------------------------------------
*/
Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

/*
|--------------------------------------------------------------------------
| PUBLIC WEBSITE (NO LOGIN REQUIRED)
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('public.home'))->name('public.home');
Route::get('/about', fn () => view('public.about'))->name('public.about');

/** ✅ Contact page GET + POST */
Route::get('/contact', fn () => view('public.contact'))->name('public.contact');
Route::post('/contact', [PublicContactMessageController::class, 'store'])->name('public.contact.store');

/** Public services */
Route::get('/services', [PublicServiceController::class, 'index'])->name('public.services.index');
Route::get('/services/{service}', [PublicServiceController::class, 'show'])->name('public.services.show');

/** ✅ Messenger booking (PUBLIC, no login) */
Route::get('/messenger-book', [MessengerBookingController::class, 'create'])->name('messenger.book.create');
Route::post('/messenger-book', [MessengerBookingController::class, 'store'])->name('messenger.book.store');
Route::get('/messenger-book/success', [MessengerBookingController::class, 'success'])->name('messenger.book.success');

/*
|--------------------------------------------------------------------------
| AUTH (GUEST)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    // ✅ Staff/Admin login
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');

    // ✅ User login (public users)
    Route::get('/userlogin', [UserLoginController::class, 'show'])->name('userlogin');
    Route::post('/userlogin', [UserLoginController::class, 'login'])->name('userlogin.submit');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // ✅ Booking flow (LOGIN REQUIRED)
    Route::get('/book/{service}', [PublicBookingController::class, 'create'])->name('public.booking.create');
    Route::get('/book/{service}/slots', [PublicBookingController::class, 'slots'])->name('public.booking.slots');
    Route::post('/book/{service}', [PublicBookingController::class, 'store'])->name('public.booking.store');

    // ✅ User Profile
    Route::get('/profile', [UserProfileController::class, 'index'])->name('profile.show');
    Route::put('/profile', [UserProfileController::class, 'update'])->name('user.profile.update');
    Route::put('/profile/password', [UserProfileController::class, 'updatePassword'])->name('user.profile.password');

    // ✅ Public user: Installment plans (read-only)
    Route::get('/my/installments', [PublicInstallmentController::class, 'index'])->name('public.installments.index');
    Route::get('/my/installments/{plan}', [PublicInstallmentController::class, 'show'])->name('public.installments.show');

    // ✅ Staff/Admin logout
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // ✅ User logout
    Route::post('/userlogout', [UserLoginController::class, 'logout'])->name('userlogout');

    // ✅ Portal redirect based on role
    Route::get('/portal', function () {
        $role = auth()->user()->role ?? 'user';

        return match ($role) {
            'admin' => redirect()->route('admin.dashboard'),
            'staff' => redirect()->route('staff.dashboard'),
            default => redirect()->route('profile.show'),
        };
    })->name('portal');

    // ✅ Approvals redirect for staff/admin (used by push notifications)
    Route::middleware('role:admin,staff')->get('/approvals', function () {
        $role = auth()->user()->role ?? 'user';

        return match ($role) {
            'admin' => redirect()->route('admin.approvals.index'),
            'staff' => redirect()->route('staff.approvals.index'),
            default => abort(403),
        };
    })->name('approvals.portal');

    // ✅ Web Push subscription endpoints (staff/admin only)
    Route::middleware('role:admin,staff')->post('/push/subscribe', [PushSubscriptionController::class, 'store'])->name('push.subscribe');
    Route::middleware('role:admin,staff')->post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->name('push.unsubscribe');

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

            // ✅ AJAX realtime snapshots (polling)
            Route::get('/live/snapshot', [AdminLiveSnapshotController::class, 'snapshot'])->name('live.snapshot');

            // Schedule (READ-ONLY)
            Route::get('/schedule', [AdminScheduleController::class, 'index'])->name('schedule.index');
            Route::get('/schedule/events', [AdminScheduleController::class, 'events'])->name('schedule.events');

            // Users / Staff Accounts (admin + staff only)
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
            Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggleActive');
            Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');
            Route::post('/users/{id}/restore', [AdminUserController::class, 'restore'])->name('users.restore');
            Route::get('/users/{user}/activity', [AdminUserController::class, 'activity'])->name('users.activity');

            Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');
            Route::get('/appointments', [AdminAppointmentController::class, 'index'])->name('appointments.index');

            // ✅ Approval Requests (same as Staff)
            Route::get('/approvals', [AdminApprovalRequestController::class, 'index'])->name('approvals.index');
            Route::get('/approvals/widget', [AdminApprovalRequestController::class, 'widget'])->name('approvals.widget');
            Route::post('/approvals/{appointment}/approve', [AdminApprovalRequestController::class, 'approve'])->name('approvals.approve');
            Route::post('/approvals/{appointment}/decline', [AdminApprovalRequestController::class, 'decline'])->name('approvals.decline');

            Route::get('/patients', [AdminPatientController::class, 'index'])->name('patients.index');
            Route::get('/patients/{patient}', [AdminPatientController::class, 'show'])->name('patients.show');

            // Doctors
            Route::get('/doctors', [AdminDoctorController::class, 'index'])->name('doctors.index');
            Route::get('/doctors/create', [AdminDoctorController::class, 'create'])->name('doctors.create');
            Route::post('/doctors', [AdminDoctorController::class, 'store'])->name('doctors.store');
            Route::get('/doctors/{doctor}/edit', [AdminDoctorController::class, 'edit'])->name('doctors.edit');
            Route::put('/doctors/{doctor}', [AdminDoctorController::class, 'update'])->name('doctors.update');
            Route::post('/doctors/{doctor}/toggle-active', [AdminDoctorController::class, 'toggleActive'])->name('doctors.toggleActive');

            // ✅ User/Patient Accounts (WEB ACCOUNTS) — Admin only
            // ✅ NO CREATE/STORE (users create via Google login)
            // ✅ FIX: force {user} param so destroy/edit/update binds correctly
            Route::post('/user-accounts/{id}/restore', [AdminUserAccountsController::class, 'restore'])->name('user_accounts.restore');

            Route::resource('user-accounts', AdminUserAccountsController::class)
                ->only(['index', 'edit', 'update', 'destroy'])
                ->parameters(['user-accounts' => 'user'])
                ->names('user_accounts');
        });

    /*
    |--------------------------------------------------------------------------
    | STAFF ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:staff')
        ->prefix('staff')
        ->name('staff.')
        ->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            // ✅ AJAX realtime snapshots (polling)
            Route::get('/live/snapshot', [StaffLiveSnapshotController::class, 'snapshot'])->name('live.snapshot');
            Route::get('/dashboard/calendar/events', [DashboardController::class, 'calendarEvents'])->name('dashboard.calendar.events');

            // ✅ Approval Requests
            Route::prefix('approvals')->name('approvals.')->group(function () {
                Route::get('/', [ApprovalRequestController::class, 'index'])->name('index');
                Route::get('/widget', [ApprovalRequestController::class, 'widget'])->name('widget');
                Route::post('/{appointment}/approve', [ApprovalRequestController::class, 'approve'])->name('approve');
                Route::post('/{appointment}/decline', [ApprovalRequestController::class, 'decline'])->name('decline');
            });

            // ✅ Contact Messages Inbox (Staff)
            Route::prefix('messages')->name('messages.')->group(function () {
                Route::get('/widget', [StaffContactMessageController::class, 'widget'])->name('widget');

                Route::get('/', [StaffContactMessageController::class, 'index'])->name('index');
                Route::get('/{message}', [StaffContactMessageController::class, 'show'])->name('show');
                Route::post('/{message}/read', [StaffContactMessageController::class, 'markRead'])->name('read');
                Route::post('/{id}/restore', [StaffContactMessageController::class, 'restore'])->name('restore');
                Route::post('/{id}/restore', [StaffContactMessageController::class, 'restore'])->name('restore');
                Route::delete('/{message}', [StaffContactMessageController::class, 'destroy'])->name('destroy');
            });

            // Patients import/export
            Route::get('/patients/export', [PatientImportExportController::class, 'export'])->name('patients.export');
            Route::post('/patients/import', [PatientImportExportController::class, 'import'])->name('patients.import');

            // ✅ PRINT Patient Information Record (PDF)
            Route::get('/patients/{patient}/print-info', [PatientController::class, 'printInfo'])->name('patients.printInfo');

            // ✅ VISITS IMPORT/TEMPLATE
            Route::get('/visits/template', [VisitImportExportController::class, 'template'])->name('visits.template');
            Route::post('/visits/import', [VisitImportExportController::class, 'import'])->name('visits.import');

            // Undo delete (restore)
            Route::post('/patients/{id}/restore', [PatientController::class, 'restore'])->name('patients.restore');
            Route::post('/visits/{id}/restore', [VisitController::class, 'restore'])->name('visits.restore');
            Route::post('/appointments/{id}/restore', [AppointmentController::class, 'restore'])->name('appointments.restore');
            Route::post('/services/{id}/restore', [ServiceController::class, 'restore'])->name('services.restore');

            // Resources
            Route::resource('patients', PatientController::class);
            Route::resource('visits', VisitController::class);
            Route::resource('appointments', AppointmentController::class);
            Route::resource('services', ServiceController::class);

            // Patient Visit History
            Route::get('/patients/{patient}/visits', [VisitController::class, 'patientVisits'])->name('patients.visits');

            // ✅ Service Folder
            Route::get('services/{service}/patients', [ServiceController::class, 'patients'])->name('services.patients');

            // Payments
            Route::prefix('payments')->name('payments.')->group(function () {
                Route::get('/', [PaymentController::class, 'index'])->name('index');
                Route::get('/choose', [PaymentController::class, 'choosePlan'])->name('choose');

                // ✅ ADD THIS (must be ABOVE /{payment} routes)
                Route::get('/cash/patient/{patient}', [PaymentController::class, 'cashPatient'])
                    ->name('cash.patient');

                Route::get('/create/cash', [PaymentController::class, 'createCash'])->name('create.cash');
                Route::post('/store/cash', [PaymentController::class, 'storeCash'])->name('store.cash');

                Route::get('/create/installment', [PaymentController::class, 'createInstallment'])->name('create.installment');
                Route::post('/store/installment', [PaymentController::class, 'storeInstallment'])->name('store.installment');

                // Undo delete (restore)
                Route::post('/{id}/restore', [PaymentController::class, 'restore'])->name('restore');

                // ⚠️ keep these LAST
                Route::post('/{id}/restore', [PaymentController::class, 'restore'])->name('restore');

                Route::get('/{payment}', [PaymentController::class, 'show'])->name('show');
                Route::get('/{payment}/edit', [PaymentController::class, 'edit'])->name('edit');
                Route::put('/{payment}', [PaymentController::class, 'update'])->name('update');
                Route::delete('/{payment}', [PaymentController::class, 'destroy'])->name('destroy');
            });


            // Installments
            Route::prefix('installments')->name('installments.')->group(function () {

                Route::get('/', [InstallmentPlanController::class, 'index'])->name('index');
                Route::get('/create', [InstallmentPlanController::class, 'create'])->name('create');
                Route::post('/', [InstallmentPlanController::class, 'store'])->name('store');

                // ✅ INSTALLMENTS IMPORT/TEMPLATE
                Route::get('/template', [InstallmentImportExportController::class, 'plansTemplate'])->name('template');
                Route::post('/import', [InstallmentImportExportController::class, 'importPlans'])->name('import');

                Route::get('/{plan}/payments/template', [InstallmentImportExportController::class, 'paymentsTemplate'])->name('payments.template');
                Route::post('/{plan}/payments/import', [InstallmentImportExportController::class, 'importPayments'])->name('payments.import');

                Route::post('/{id}/restore', [InstallmentPlanController::class, 'restore'])->name('restore');

                Route::get('/{plan}', [InstallmentPlanController::class, 'show'])->name('show');
                Route::get('/{plan}/edit', [InstallmentPlanController::class, 'edit'])->name('edit');
                Route::put('/{plan}', [InstallmentPlanController::class, 'update'])->name('update');
                Route::delete('/{plan}', [InstallmentPlanController::class, 'destroy'])->name('destroy');

                Route::get('/{plan}/pay', [InstallmentPaymentController::class, 'create'])->name('pay');
                Route::post('/{plan}/pay', [InstallmentPaymentController::class, 'store'])->name('pay.store');

                Route::post('/{plan}/complete', [InstallmentPlanController::class, 'complete'])->name('complete');
                Route::post('/{plan}/reopen', [InstallmentPlanController::class, 'reopen'])->name('reopen');

                Route::get('/{plan}/payments/{payment}/edit', [InstallmentPaymentController::class, 'edit'])->name('payments.edit');
                Route::put('/{plan}/payments/{payment}', [InstallmentPaymentController::class, 'update'])->name('payments.update');
            });
        });
});