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

Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    return (auth()->user()->role ?? 'staff') === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('dashboard');
})->name('root');

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

    /*
    |--------------------------------------------------------------------------
    | ADMIN ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:admin')
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {

            Route::get('/dashboard', [AdminDashboardController::class, 'index'])
                ->name('dashboard');

            // ✅ Schedule (READ-ONLY)
            Route::get('/schedule', [AdminScheduleController::class, 'index'])
                ->name('schedule.index');

            Route::get('/schedule/events', [AdminScheduleController::class, 'events'])
                ->name('schedule.events');

            // ✅ Users / Staff Accounts
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::get('/users/create', [AdminUserController::class, 'create'])->name('users.create');
            Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
            Route::get('/users/{user}/edit', [AdminUserController::class, 'edit'])->name('users.edit');
            Route::put('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
            Route::post('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive'])->name('users.toggleActive');
            Route::get('/analytics', [AdminAnalyticsController::class, 'index'])->name('analytics.index');

            Route::get('/appointments', [AdminAppointmentController::class, 'index'])
    ->name('appointments.index');
            // Later:
            // Route::get('/reports', ...)->name('reports');
            // Route::get('/settings', ...)->name('settings');
            Route::get('/patients', [AdminPatientController::class, 'index'])->name('patients.index');
            Route::get('/doctors', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'index'])->name('doctors.index');
            Route::get('/doctors/create', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'create'])->name('doctors.create');
            Route::post('/doctors', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'store'])->name('doctors.store');
            Route::get('/doctors/{doctor}/edit', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'edit'])->name('doctors.edit');
            Route::put('/doctors/{doctor}', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'update'])->name('doctors.update');
            Route::post('/doctors/{doctor}/toggle-active', [\App\Http\Controllers\Admin\AdminDoctorController::class, 'toggleActive'])->name('doctors.toggleActive');

Route::get('/patients/{patient}', [AdminPatientController::class, 'show'])->name('patients.show');
        });
        



    /*
    |--------------------------------------------------------------------------
    | STAFF ROUTES
    |--------------------------------------------------------------------------
    */
    Route::middleware('role:staff')->group(function () {

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        Route::get('/dashboard/calendar/events', [DashboardController::class, 'calendarEvents'])
            ->name('dashboard.calendar.events');

        Route::get('/patients/export', [PatientImportExportController::class, 'export'])
            ->name('patients.export');

        Route::post('/patients/import', [PatientImportExportController::class, 'import'])
            ->name('patients.import');

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

        /*
        |--------------------------------------------------------------------------
        | INSTALLMENTS
        |--------------------------------------------------------------------------
        */
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
