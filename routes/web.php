<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/dashboard', [DashboardController::class, 'viewDashboard']) -> name('dashboard');
Route::get('/login', [DashboardController::class, 'viewLogin']);

// ROUTE UNTUK PROSES LOGIN
Route::post('/login', [DashboardController::class, 'login'])->name('login.process');
