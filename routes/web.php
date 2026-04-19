<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/login', [DashboardController::class, 'viewLoginPage']);
Route::get('/dashboard', [DashboardController::class, 'viewDashboardPage']) -> name('dashboard');
Route::get('/input-data-pasien', [DashboardController::class, 'viewInputDataPasienPage']) -> name('input-data-pasien');
Route::get('/laporan', [DashboardController::class, 'viewLaporanPage']) -> name('laporan');

// ROUTE UNTUK PROSES LOGIN
Route::post('/login', [DashboardController::class, 'login'])->name('login.process');
