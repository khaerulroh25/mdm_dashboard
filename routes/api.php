<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\DpcApiController;

/*
|--------------------------------------------------------------------------
| Standalone Android DPC (Device Policy Controller) API Routes
|--------------------------------------------------------------------------
*/

Route::prefix('v1/dpc')->group(function () {
    // 1. Device Enrollment (Public with Valid Enrollment Token)
    Route::post('/enroll', [DpcApiController::class, 'enroll'])->name('dpc.enroll');

    // 2. Authenticated DPC Endpoints (Protected by Device Token)
    Route::middleware('auth.dpc')->group(function () {
        // Fetch active policy configuration
        Route::get('/policy', [DpcApiController::class, 'getPolicy'])->name('dpc.policy');

        // Periodic telemetry and status report
        Route::post('/heartbeat', [DpcApiController::class, 'heartbeat'])->name('dpc.heartbeat');

        // Pending commands queue
        Route::get('/commands', [DpcApiController::class, 'getPendingCommands'])->name('dpc.commands');
        Route::post('/commands/{id}/ack', [DpcApiController::class, 'acknowledgeCommand'])->name('dpc.commands.ack');
    });
});
