<?php

use App\Http\Controllers\Api\InstructionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\ManajemenAlatController;
use App\Http\Controllers\SuperadminLaporanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    return view('pages.landing');
});

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

    // Profile Routes (all roles)
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Nakes Routes
    Route::prefix('nakes')->middleware('role:nakes')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'viewDashboardPage'])->name('dashboard');
        Route::post('/device-config', [DashboardController::class, 'saveDeviceConfig'])->name('nakes.device-config.store');
        Route::delete('/device-config', [DashboardController::class, 'resetDeviceConfig'])->name('nakes.device-config.reset');
        Route::patch('/device-status', [DashboardController::class, 'toggleDeviceStatus'])->name('nakes.device-status.toggle');
        Route::get('/input-data-pasien', [DashboardController::class, 'viewInputDataPasienPage'])->name('input-data-pasien');
        Route::post('/input-data-pasien', [PatientController::class, 'store'])->name('input-data-pasien.store');
        // Route::get('/laporan', [DashboardController::class, 'viewLaporanPage'])->name('laporan');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
        Route::get('/laporan/session-data', [LaporanController::class, 'sessionData'])->name('laporan.session-data');
        Route::get('/laporan/pdf', [LaporanController::class, 'pdf'])->name('laporan.pdf');
        Route::get('/instruksi', function () {
            return view('pages.nakes.instruksi');
        })->name('nakes.instruksi');

        Route::get('/monitoring', function () {
            return view('pages.nakes.monitoring');
        })->name('nakes.monitoring');
    });

    // Dokter Routes
    Route::prefix('dokter')->middleware('role:dokter')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'viewDashboardPage'])->name('dokter.dashboard');
        Route::post('/select-device', [DashboardController::class, 'selectDevice'])->name('dokter.select-device');
        Route::delete('/deselect-device', [DashboardController::class, 'deselectDevice'])->name('dokter.deselect-device');
        Route::get('/input-data-pasien', [DashboardController::class, 'viewInputDataPasienPage'])->name('dokter.input-data-pasien');
        Route::get('/laporan', [LaporanController::class, 'index'])->name('dokter.laporan');
        Route::get('/laporan/pdf', [LaporanController::class, 'pdf'])->name('dokter.laporan.pdf');

        Route::get('/instruksi', function () {
            return view('pages.dokter.instruksi');
        })->name('dokter.instruksi');

        Route::get('/monitoring', function () {
            return view('pages.dokter.monitoring');
        })->name('dokter.monitoring');

        Route::get('/monitoring-3d', function () {
            return view('pages.dokter.monitor-3d');
        })->name('dokter.monitoring-3d');
    });

    // Superadmin Routes
    Route::prefix('superadmin')->middleware('role:superadmin')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'viewDashboardPage'])->name('superadmin.dashboard');
        Route::get('/manajemen-alat', [ManajemenAlatController::class, 'index'])->name('superadmin.manajemen-alat');
        Route::post('/manajemen-alat', [ManajemenAlatController::class, 'store'])->name('superadmin.manajemen-alat.store');
        Route::delete('/manajemen-alat/{device_id}', [ManajemenAlatController::class, 'destroy'])->name('superadmin.manajemen-alat.destroy');
        Route::get('/manajemen-alat/{device_id}', [ManajemenAlatController::class, 'show'])->name('superadmin.manajemen-alat.show');
        Route::get('/manajemen-user', [DashboardController::class, 'viewManajemenUserPage'])->name('superadmin.manajemen-user');
        Route::post('/manajemen-user', [UserController::class, 'store'])->name('superadmin.manajemen-user.store');
        Route::delete('/manajemen-user/{user}', [UserController::class, 'destroy'])->name('superadmin.manajemen-user.destroy');
        Route::get('/input-data-pasien', [DashboardController::class, 'viewInputDataPasienPage'])->name('superadmin.input-data-pasien');
        Route::get('/laporan', [SuperadminLaporanController::class, 'index'])->name('superadmin.laporan');
        Route::get('/laporan/pdf', [SuperadminLaporanController::class, 'pdf'])->name('superadmin.laporan.pdf');
    });

    // Device list endpoint (for dashboard polling)
    Route::get('/api/devices', [DashboardController::class, 'getDevicesApi']);

    // Online users count endpoint (for superadmin dashboard polling)
    Route::get('/api/online-users-count', function () {
        $count = \DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes(5)->timestamp)
            ->count();
        return response()->json(['success' => true, 'count' => $count]);
    });
});
