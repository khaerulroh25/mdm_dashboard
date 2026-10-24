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
use App\Http\Controllers\BranchController;
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

    // Devices Management
    Route::prefix('devices')->name('devices.')->group(function () {
        Route::get('/', [DeviceController::class, 'index'])->name('index')->middleware('permission:devices.view');
        Route::post('/sync', [DeviceController::class, 'sync'])->name('sync')->middleware('permission:devices.sync');
        Route::get('/{device}', [DeviceController::class, 'show'])->name('show')->middleware('permission:devices.view');
        Route::post('/{device}/command', [DeviceController::class, 'issueCommand'])->name('command')->middleware('permission:devices.command');
        Route::post('/{device}/policy', [DeviceController::class, 'updatePolicy'])->name('policy')->middleware('permission:devices.command');
        Route::post('/{device}/branch', [DeviceController::class, 'updateBranch'])->name('branch')->middleware('permission:branches.manage,devices.edit');
        Route::post('/{device}/rename-model', [DeviceController::class, 'updateMarketingName'])->name('rename-model')->middleware('permission:devices.edit');
        Route::delete('/{device}', [DeviceController::class, 'unenroll'])->name('unenroll')->middleware('permission:devices.delete');
        Route::delete('/{device}/permanent', [DeviceController::class, 'destroyPermanent'])->name('destroy-permanent')->middleware('permission:devices.delete');
    });

    // Device Owners
    Route::post('/owners', [DeviceOwnerController::class, 'store'])->name('owners.store')->middleware('permission:devices.edit');

    // Policies
    Route::prefix('policies')->name('policies.')->group(function () {
        Route::get('/', [PolicyController::class, 'index'])->name('index')->middleware('permission:policies.view');
        Route::get('/create', [PolicyController::class, 'create'])->name('create')->middleware('permission:policies.manage');
        Route::post('/', [PolicyController::class, 'store'])->name('store')->middleware('permission:policies.manage');
        Route::get('/{id}/edit', [PolicyController::class, 'edit'])->name('edit')->middleware('permission:policies.manage');
        Route::put('/{id}', [PolicyController::class, 'update'])->name('update')->middleware('permission:policies.manage');
        Route::delete('/{id}', [PolicyController::class, 'destroy'])->name('destroy')->middleware('permission:policies.manage');
        Route::post('/{id}/apps', [PolicyController::class, 'addApp'])->name('add-app')->middleware('permission:policies.manage');
    });

    // Google Play Apps
    Route::prefix('apps')->name('apps.')->group(function () {
        Route::get('/', [GooglePlayController::class, 'index'])->name('index')->middleware('permission:apps.view');
        Route::get('/search', [GooglePlayController::class, 'search'])->name('search')->middleware('permission:apps.create,apps.view');
        Route::get('/lookup', [GooglePlayController::class, 'lookup'])->name('lookup')->middleware('permission:apps.create,apps.view');
        Route::post('/', [GooglePlayController::class, 'store'])->name('store')->middleware('permission:apps.create');
        Route::delete('/{id}', [GooglePlayController::class, 'destroy'])->name('destroy')->middleware('permission:apps.delete');
    });

    // Enrollment QR
    Route::prefix('enrollment')->name('enrollment.')->group(function () {
        Route::get('/', [EnrollmentController::class, 'index'])->name('index')->middleware('permission:enrollment.manage');
        Route::post('/generate', [EnrollmentController::class, 'generate'])->name('generate')->middleware('permission:enrollment.manage');
    });

    // Audit Logs
    Route::get('/audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index')->middleware('permission:audit_logs.view');

    // Master Branches Management
    Route::resource('branches', BranchController::class)->except(['show'])->middleware('permission:branches.manage');

    // Users Management
    Route::resource('users', UserController::class)->middleware('permission:users.manage');
});
