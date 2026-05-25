<?php

/**
 * Routes untuk role: Dokter
 * Prefix: /dokter
 * Middleware: auth, role:dokter
 */

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\RekamMedisController;

// Dashboard & Device Selection
Route::get('/dashboard', [DashboardController::class, 'viewDashboardPage'])->name('dokter.dashboard');
Route::post('/select-device', [DashboardController::class, 'selectDevice'])->name('dokter.select-device');
Route::delete('/deselect-device', [DashboardController::class, 'deselectDevice'])->name('dokter.deselect-device');

// Input Data Pasien
Route::get('/input-data-pasien', [DashboardController::class, 'viewInputDataPasienPage'])->name('dokter.input-data-pasien');

// Laporan
Route::get('/laporan', [LaporanController::class, 'index'])->name('dokter.laporan');
Route::get('/laporan/pdf', [LaporanController::class, 'pdf'])->name('dokter.laporan.pdf');

// Rekam Medis
Route::get('/rekam-medis', [RekamMedisController::class, 'index'])->name('dokter.rekam-medis');
Route::get('/rekam-medis/{id}', [RekamMedisController::class, 'show'])->name('dokter.rekam-medis.show');
Route::get('/rekam-medis/{id}/pdf', [RekamMedisController::class, 'pdf'])->name('dokter.rekam-medis.pdf');

// Instruksi
Route::get('/instruksi', function () {
    return view('pages.dokter.instruksi');
})->name('dokter.instruksi');

// Monitoring
Route::get('/monitoring', function () {
    return view('pages.dokter.monitoring');
})->name('dokter.monitoring');

// Monitoring 3D
Route::get('/monitoring-3d', function () {
    return view('pages.dokter.monitor-3d');
})->name('dokter.monitoring-3d');
