<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\InstallmentPlanController;
use App\Http\Controllers\InstallmentPaymentController;
use App\Http\Controllers\PatientImportExportController;

use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminScheduleController;
use App\Http\Controllers\Admin\AdminAppointmentController;
use App\Http\Controllers\Admin\AdminPatientController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Public\PublicServiceController;
use App\Http\Controllers\Public\PublicBookingController;


/*
|--------------------------------------------------------------------------
| PUBLIC WEBSITE (NO LOGIN REQUIRED)
|--------------------------------------------------------------------------
| UI first — these can be Blade views for now.
| Later we’ll wire controllers + booking logic.
*/
/*
|--------------------------------------------------------------------------|
| PUBLIC WEBSITE (NO LOGIN REQUIRED)
|--------------------------------------------------------------------------|
*/
Route::get('/', fn() => view('public.home'))->name('public.home');
Route::get('/about', fn() => view('public.about'))->name('public.about');
Route::get('/contact', fn() => view('public.contact'))->name('public.contact');

/** ✅ Public services pull from DB (staff-managed services table) */
Route::get('/services', [PublicServiceController::class, 'index'])->name('public.services.index');
Route::get('/services/{service}', [PublicServiceController::class, 'show'])->name('public.services.show');

/** ✅ Booking flow */
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

    // ✅ Portal redirect based on role (Admin → /admin/dashboard, Staff → /staff/dashboard)
    Route::get('/portal', function () {
        return (auth()->user()->role ?? 'staff') === 'admin'
            ? redirect()->route('admin.dashboard')
            : redirect()->route('dashboard');
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

            // ✅ Schedule (READ-ONLY)
            Route::get('/schedule', [AdminScheduleController::class, 'index'])->name('schedule.index');
            Route::get('/schedule/events', [AdminScheduleController::class, 'events'])->name('schedule.events');

            // ✅ Users / Staff Accounts
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
            Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggleActive');

            // ✅ Analytics
            Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');

            // ✅ Appointments
            Route::get('/appointments', [AdminAppointmentController::class, 'index'])->name('appointments.index');

            // ✅ Patients
            Route::get('/patients', [AdminPatientController::class, 'index'])->name('patients.index');
            Route::get('/patients/{patient}', [AdminPatientController::class, 'show'])->name('patients.show');

            // ✅ Doctors
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
    | ✅ Prefix all staff URLs with /staff to avoid conflict with public site.
    | ✅ Route NAMES stay the same (dashboard, services.index, etc.) so your Blade links won’t break.
    */
    Route::middleware('role:staff')
        ->prefix('staff')
        ->group(function () {

            Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

            Route::get('/dashboard/calendar/events', [DashboardController::class, 'calendarEvents'])
                ->name('dashboard.calendar.events');

            Route::get('/patients/export', [PatientImportExportController::class, 'export'])->name('patients.export');
            Route::post('/patients/import', [PatientImportExportController::class, 'import'])->name('patients.import');

            Route::resource('patients', PatientController::class);
            Route::resource('visits', VisitController::class);
            Route::resource('appointments', AppointmentController::class);
            Route::resource('services', ServiceController::class);

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
