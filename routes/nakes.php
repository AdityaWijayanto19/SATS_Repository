<?php

/**
 * Routes untuk role: Nakes (Perawat)
 * Prefix: /nakes
 * Middleware: auth, role:nakes
 */

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PatientController;

// Dashboard & Device Config
Route::get('/dashboard', [DashboardController::class, 'viewDashboardPage'])->name('dashboard');
Route::post('/device-config', [DashboardController::class, 'saveDeviceConfig'])->name('nakes.device-config.store');
Route::delete('/device-config', [DashboardController::class, 'resetDeviceConfig'])->name('nakes.device-config.reset');
Route::patch('/device-status', [DashboardController::class, 'toggleDeviceStatus'])->name('nakes.device-status.toggle');

// Input Data Pasien
Route::get('/input-data-pasien', [DashboardController::class, 'viewInputDataPasienPage'])->name('input-data-pasien');
Route::post('/input-data-pasien', [PatientController::class, 'store'])->name('input-data-pasien.store');

// Laporan
Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
Route::get('/laporan/session-data', [LaporanController::class, 'sessionData'])->name('laporan.session-data');
Route::get('/laporan/pdf', [LaporanController::class, 'pdf'])->name('laporan.pdf');

// Instruksi
Route::get('/instruksi', function () {
    return view('pages.nakes.instruksi');
})->name('nakes.instruksi');

// Monitoring
Route::get('/monitoring', function () {
    return view('pages.nakes.monitoring');
})->name('nakes.monitoring');
