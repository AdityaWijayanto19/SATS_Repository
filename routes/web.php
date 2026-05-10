<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\SuperadminLaporanController;

// Auth Route
Route::get('/login', [AuthController::class, 'viewLoginPage'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');

Route::get('/reset-password', [AuthController::class, 'showResetPassword'])
    ->name('password.reset')
    ->middleware('signed');
Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');

// Protected Routes
Route::middleware(['auth'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Nakes Routes
    Route::prefix('nakes')->middleware('role:nakes')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'viewDashboardPage'])->name('dashboard');
        Route::get('/input-data-pasien', [DashboardController::class, 'viewInputDataPasienPage'])->name('input-data-pasien');
        // Route::get('/laporan', [DashboardController::class, 'viewLaporanPage'])->name('laporan');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/pdf', [LaporanController::class, 'pdf'])->name('laporan.pdf');
    });

    // Dokter Routes
    Route::prefix('dokter')->middleware('role:dokter')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'viewDashboardPage'])->name('dokter.dashboard');
        Route::get('/input-data-pasien', [DashboardController::class, 'viewInputDataPasienPage'])->name('dokter.input-data-pasien');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('dokter.laporan');
        Route::get('/laporan/pdf', [LaporanController::class, 'pdf'])->name('dokter.laporan.pdf');
    });

    // Superadmin Routes
    Route::prefix('superadmin')->middleware('role:superadmin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'viewDashboardPage'])->name('superadmin.dashboard');
        Route::get('/manajemen-alat', [DashboardController::class, 'viewManajemenAlatPage'])->name('superadmin.manajemen-alat');
        Route::get('/manajemen-user', [DashboardController::class, 'viewManajemenUserPage'])->name('superadmin.manajemen-user');
        Route::get('/input-data-pasien', [DashboardController::class, 'viewInputDataPasienPage'])->name('superadmin.input-data-pasien');
        Route::get('/laporan', [SuperadminLaporanController::class, 'index'])->name('superadmin.laporan');
        Route::get('/laporan/pdf', [SuperadminLaporanController::class, 'pdf'])->name('superadmin.laporan.pdf');
    });
});