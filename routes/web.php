<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeviceController;
use App\Http\Controllers\DeviceOwnerController;
use App\Http\Controllers\PolicyController;
use App\Http\Controllers\GooglePlayController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| Authentication Routes (Public)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'login'])->name('login');
    Route::post('/login', [AuthController::class, 'loginAction'])->name('login.action');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

/*
|--------------------------------------------------------------------------
| Authenticated Dashboard & Module Routes
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Devices Fleet
    Route::prefix('devices')->name('devices.')->group(function () {
        Route::get('/', [DeviceController::class, 'index'])->name('index');
        Route::post('/sync', [DeviceController::class, 'sync'])->name('sync')->middleware('role:admin,operator');
        Route::get('/{device}', [DeviceController::class, 'show'])->name('show');
        Route::post('/{device}/command', [DeviceController::class, 'issueCommand'])->name('command')->middleware('role:admin,operator');
        Route::post('/{device}/policy', [DeviceController::class, 'updatePolicy'])->name('policy')->middleware('role:admin,operator');
        Route::delete('/{device}', [DeviceController::class, 'unenroll'])->name('unenroll')->middleware('role:admin');
    });

    // Device Owners
    Route::post('/owners', [DeviceOwnerController::class, 'store'])->name('owners.store')->middleware('role:admin,operator');

    // Policies
    Route::prefix('policies')->name('policies.')->group(function () {
        Route::get('/', [PolicyController::class, 'index'])->name('index');
        Route::get('/create', [PolicyController::class, 'create'])->name('create')->middleware('role:admin');
        Route::post('/', [PolicyController::class, 'store'])->name('store')->middleware('role:admin');
        Route::get('/{id}/edit', [PolicyController::class, 'edit'])->name('edit')->middleware('role:admin');
        Route::put('/{id}', [PolicyController::class, 'update'])->name('update')->middleware('role:admin');
        Route::delete('/{id}', [PolicyController::class, 'destroy'])->name('destroy')->middleware('role:admin');
        Route::post('/{id}/apps', [PolicyController::class, 'addApp'])->name('add-app')->middleware('role:admin');
    });

    // Google Play Apps
    Route::prefix('apps')->name('apps.')->group(function () {
        Route::get('/', [GooglePlayController::class, 'index'])->name('index');
        Route::get('/search', [GooglePlayController::class, 'search'])->name('search');
        Route::post('/', [GooglePlayController::class, 'store'])->name('store')->middleware('role:admin,operator');
        Route::delete('/{id}', [GooglePlayController::class, 'destroy'])->name('destroy')->middleware('role:admin');
    });

    // Enrollment QR
    Route::prefix('enrollment')->name('enrollment.')->group(function () {
        Route::get('/', [EnrollmentController::class, 'index'])->name('index')->middleware('role:admin,operator');
        Route::post('/generate', [EnrollmentController::class, 'generate'])->name('generate')->middleware('role:admin,operator');
    });

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index')->middleware('role:admin,auditor');

    // Users Management (Admin Only)
    Route::resource('users', UserController::class)->middleware('role:admin');
});
