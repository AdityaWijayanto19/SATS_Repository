<?php

/**
 * Routes untuk role: Superadmin
 * Prefix: /superadmin
 * Middleware: auth, role:superadmin
 */

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ManajemenAlatController;
use App\Http\Controllers\SuperadminLaporanController;
use App\Http\Controllers\UserController;

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'viewDashboardPage'])->name('superadmin.dashboard');

// Manajemen Alat
Route::get('/manajemen-alat', [ManajemenAlatController::class, 'index'])->name('superadmin.manajemen-alat');
Route::post('/manajemen-alat', [ManajemenAlatController::class, 'store'])->name('superadmin.manajemen-alat.store');
Route::delete('/manajemen-alat/{device_id}', [ManajemenAlatController::class, 'destroy'])->name('superadmin.manajemen-alat.destroy');
Route::get('/manajemen-alat/{device_id}', [ManajemenAlatController::class, 'show'])->name('superadmin.manajemen-alat.show');

// Manajemen User
Route::get('/manajemen-user', [DashboardController::class, 'viewManajemenUserPage'])->name('superadmin.manajemen-user');
Route::post('/manajemen-user', [UserController::class, 'store'])->name('superadmin.manajemen-user.store');
Route::delete('/manajemen-user/{user}', [UserController::class, 'destroy'])->name('superadmin.manajemen-user.destroy');

// Input Data Pasien
Route::get('/input-data-pasien', [DashboardController::class, 'viewInputDataPasienPage'])->name('superadmin.input-data-pasien');

// Laporan
Route::get('/laporan', [SuperadminLaporanController::class, 'index'])->name('superadmin.laporan');
Route::get('/laporan/pdf', [SuperadminLaporanController::class, 'pdf'])->name('superadmin.laporan.pdf');
