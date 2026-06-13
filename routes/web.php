<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\RackController;
use App\Http\Controllers\Web\ItemController;
use App\Http\Controllers\Web\CycleCountWebController;
use App\Http\Controllers\Web\UserController;
use App\Http\Controllers\Web\SettingController;

// RUTE PUBLIK
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return redirect()->route('dashboard');
});

// RUTE WAJIB LOGIN (AUTH)
Route::middleware(['auth'])->group(function () {

    // Rute Dasar (Semua Role Web Bisa Akses)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/settings', [SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings/password', [SettingController::class, 'updatePassword'])->name('settings.password');

    // DASHBOARD MANAGER & OWNER
    Route::middleware(['role:manager,owner'])->group(function () {
        // Route::get('/manager/dashboard', [DashboardController::class, 'manager'])->name('manager.dashboard');
        Route::get('/management/dashboard', [DashboardController::class, 'manager'])->name('management.dashboard');
    });

    // Master Data (Semua bisa melihat Rak & Item)
    Route::resource('racks', RackController::class);
    Route::resource('items', ItemController::class);

    //OPERASIONAL (Admin, Manager, Owner, Supervisor)
    Route::middleware(['role:admin,manager,owner,supervisor'])->group(function () {
        // Manajemen Kehadiran (Semua bisa lihat tabel dan ubah status hadir)
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users/{id}/toggle-presence', [UserController::class, 'togglePresence'])->name('users.toggle');
        
        // Cycle Count Dasar
        Route::get('cycle', [CycleCountWebController::class, 'index'])->name('cycle.index');
        Route::get('cycle/{id}', [CycleCountWebController::class, 'show'])->name('cycle.show');
    });

    // EKSEKUTIF / TOP LEVEL (Admin, Manager, Owner)
    // Fitur Tambah/Hapus Staf, Import Barang, Laporan Keuangan
    Route::middleware(['role:admin,manager,owner'])->group(function () {
        // CRUD Staf (Supervisor DILARANG masuk sini)
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        
        // Import Data & Laporan
        Route::post('/items/import', [ItemController::class, 'import'])->name('items.import');
        Route::post('/items/sync', [ItemController::class, 'sync'])->name('items.sync');
        Route::post('cycle/{id}/review', [CycleCountWebController::class, 'review'])->name('cycle.review');
        Route::get('/cycle/{id}/export-accurate', [CycleCountWebController::class, 'exportAccurate'])->name('cycle.export.accurate');
        Route::get('cycle/{id}/pdf', [CycleCountWebController::class, 'exportPdf'])->name('cycle.pdf');
        Route::get('/cycle/{id}/export-excel-laporan', [CycleCountWebController::class, 'exportExcelLaporan'])->name('cycle.export.excel');
    });

    // PELAKSANA LAPANGAN (Admin & Supervisor)
    // Fitur Buat Jadwal, Approve Hasil Hitung, Request Recount
    Route::middleware(['role:admin,supervisor'])->group(function () {
        Route::get('cycle/schedule', [CycleCountWebController::class, 'createSchedule'])->name('cycle.createSchedule');
        Route::post('cycle/schedule', [CycleCountWebController::class, 'storeSchedule'])->name('cycle.storeSchedule');
        Route::post('/cycle/generate-auto', [CycleCountWebController::class, 'runAutoGenerator'])->name('cycle.generate-auto');
        
        // Aksi spesifik pada Cycle Count
        Route::post('cycle/{id}/sync', [CycleCountWebController::class, 'syncItems'])->name('cycle.sync');
        Route::post('cycle/{id}/store-detail', [CycleCountWebController::class, 'storeDetail'])->name('cycle.storeDetail');
        Route::post('cycle/recount/{id}', [CycleCountWebController::class, 'requestRecount'])->name('cycle.recount');
        Route::post('cycle/{id}/approve', [CycleCountWebController::class, 'approve'])->name('cycle.approve');
        Route::delete('cycle/{id}', [CycleCountWebController::class, 'destroy'])->name('cycle.destroy');
    });
});