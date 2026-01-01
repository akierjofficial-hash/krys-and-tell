<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;

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

// Admin controllers
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminScheduleController;
use App\Http\Controllers\Admin\AdminAppointmentController;
use App\Http\Controllers\Admin\AdminPatientController;
use App\Http\Controllers\Admin\AdminAnalyticsController;

// Public controllers
use App\Http\Controllers\Public\PublicServiceController;
use App\Http\Controllers\Public\PublicBookingController;

/*
|--------------------------------------------------------------------------
| PUBLIC WEBSITE (NO LOGIN REQUIRED)
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => view('public.home'))->name('public.home');
Route::get('/about', fn () => view('public.about'))->name('public.about');
Route::get('/contact', fn () => view('public.contact'))->name('public.contact');

/** Public services */
Route::get('/services', [PublicServiceController::class, 'index'])->name('public.services.index');
Route::get('/services/{service}', [PublicServiceController::class, 'show'])->name('public.services.show');

/** Booking flow */
Route::get('/book/{service}', [PublicBookingController::class, 'create'])->name('public.booking.create');
Route::get('/book/{service}/slots', [PublicBookingController::class, 'slots'])->name('public.booking.slots');
Route::post('/book/{service}', [PublicBookingController::class, 'store'])->name('public.booking.store');
Route::get('/booking/success/{appointment}', [PublicBookingController::class, 'success'])->name('public.booking.success');

/*
|--------------------------------------------------------------------------
| AUTH (GUEST)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.submit');
});

/*
|--------------------------------------------------------------------------
| AUTHENTICATED ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // ✅ Portal redirect based on role
    Route::get('/portal', function () {
        return (auth()->user()->role ?? 'staff') === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('staff.dashboard');
    })->name('portal');

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

            // Schedule (READ-ONLY)
            Route::get('/schedule', [AdminScheduleController::class, 'index'])->name('schedule.index');
            Route::get('/schedule/events', [AdminScheduleController::class, 'events'])->name('schedule.events');

            // Users / Staff Accounts
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
            Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggleActive');

            // ✅ NEW: Activity Log per user
            Route::get('/users/{user}/activity', [AdminUserController::class, 'activity'])->name('users.activity');

            

            // Analytics
            Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');

            // Appointments
            Route::get('/appointments', [AdminAppointmentController::class, 'index'])->name('appointments.index');

            // Patients
            Route::get('/patients', [AdminPatientController::class, 'index'])->name('patients.index');
            Route::get('/patients/{patient}', [AdminPatientController::class, 'show'])->name('patients.show');

            // Doctors
            Route::get('/doctors', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'index'])->name('doctors.index');
            Route::get('/doctors/create', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'create'])->name('doctors.create');
            Route::post('/doctors', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'store'])->name('doctors.store');
            Route::get('/doctors/{doctor}/edit', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'edit'])->name('doctors.edit');
            Route::put('/doctors/{doctor}', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'update'])->name('doctors.update');
            Route::post('/doctors/{doctor}/toggle-active', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'toggleActive'])->name('doctors.toggleActive');
        });

    /*
    |--------------------------------------------------------------------------
    | STAFF ROUTES
    |--------------------------------------------------------------------------
    | ✅ URL prefix: /staff/...
    | ✅ Name prefix: staff....
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:staff')
        ->prefix('staff')
        ->name('staff.')
        ->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            Route::get('/dashboard/calendar/events', [DashboardController::class, 'calendarEvents'])
                ->name('dashboard.calendar.events');

            // ✅ Approval Requests
            Route::prefix('approvals')->name('approvals.')->group(function () {
                Route::get('/', [ApprovalRequestController::class, 'index'])->name('index');
                Route::get('/widget', [ApprovalRequestController::class, 'widget'])->name('widget');
                Route::post('/{appointment}/approve', [ApprovalRequestController::class, 'approve'])->name('approve');
                Route::post('/{appointment}/decline', [ApprovalRequestController::class, 'decline'])->name('decline');
            });

            // Patients import/export
            Route::get('/patients/export', [PatientImportExportController::class, 'export'])->name('patients.export');
            Route::post('/patients/import', [PatientImportExportController::class, 'import'])->name('patients.import');

            // Resources (names become staff.patients.*, staff.visits.*, etc.)
            Route::resource('patients', PatientController::class);
            Route::resource('visits', VisitController::class);
            Route::resource('appointments', AppointmentController::class);
            Route::resource('services', ServiceController::class);

            // Payments
            Route::prefix('payments')->name('payments.')->group(function () {
                Route::get('/', [PaymentController::class, 'index'])->name('index');
                Route::get('/choose', [PaymentController::class, 'choosePlan'])->name('choose');

                Route::get('/create/cash', [PaymentController::class, 'createCash'])->name('create.cash');
                Route::post('/store/cash', [PaymentController::class, 'storeCash'])->name('store.cash');

                Route::get('/create/installment', [PaymentController::class, 'createInstallment'])->name('create.installment');
                Route::post('/store/installment', [PaymentController::class, 'storeInstallment'])->name('store.installment');

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

                Route::get('/{plan}', [InstallmentPlanController::class, 'show'])->name('show');
                Route::get('/{plan}/edit', [InstallmentPlanController::class, 'edit'])->name('edit');
                Route::put('/{plan}', [InstallmentPlanController::class, 'update'])->name('update');
                Route::delete('/{plan}', [InstallmentPlanController::class, 'destroy'])->name('destroy');

                Route::get('/{plan}/pay', [InstallmentPaymentController::class, 'create'])->name('pay');
                Route::post('/{plan}/pay', [InstallmentPaymentController::class, 'store'])->name('pay.store');
            });
        });
});
