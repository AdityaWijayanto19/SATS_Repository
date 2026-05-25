<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;

// ============================================================
// Public Routes
// ============================================================

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

// ============================================================
// Protected Routes (Authenticated)
// ============================================================

Route::middleware(['auth'])->group(function () {

    // Logout
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Profile (semua role)
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

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

    // ============================================================
    // Role-specific Routes
    // ============================================================

    // Nakes Routes — prefix: /nakes
    Route::prefix('nakes')->middleware('role:nakes')->group(function () {
        require __DIR__ . '/nakes.php';
    });

    // Dokter Routes — prefix: /dokter
    Route::prefix('dokter')->middleware('role:dokter')->group(function () {
        require __DIR__ . '/dokter.php';
    });

    // Superadmin Routes — prefix: /superadmin
    Route::prefix('superadmin')->middleware('role:superadmin')->group(function () {
        require __DIR__ . '/superadmin.php';
    });
});
