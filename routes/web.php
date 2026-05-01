<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;

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

    // Superadmin Routes
    Route::prefix('superadmin')->middleware('role:superadmin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'viewDashboardPage'])->name('superadmin.dashboard');
        Route::get('/input-data-pasien', [DashboardController::class, 'viewInputDataPasienPage'])->name('superadmin.input-data-pasien');
        Route::get('/laporan', [DashboardController::class, 'viewLaporanPage'])->name('superadmin.laporan');
    });
});