<?php

use App\Http\Controllers\CycleCountController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SyncController;
use App\Http\Controllers\Api\AuthController;

Route::post('/login', [AuthController::class, 'loginApi']); // Route ini di luar middleware auth:api

Route::middleware(['auth:api'])->group(function () {

    // API untuk aplikasi Flutter staf gudang
    Route::get('/sync/pull', [SyncController::class, 'pullMasterData']);
    Route::post('/sync/push', [SyncController::class, 'pushCycleCount']);

    Route::post('/cycle/start/{rack_id}', [CycleCountController::class, 'startCycle']);
    Route::post('/cycle/detail', [CycleCountController::class, 'storeDetail']);
    Route::get('/cycle/export/{cycle_id}', [CycleCountController::class, 'exportAdjustment']);

    Route::post('/change-password', [AuthController::class, 'changePassword']);
});