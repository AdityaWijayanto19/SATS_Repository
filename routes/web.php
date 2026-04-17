<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

// Ini welcome default
// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/dashboard', [DashboardController::class, 'viewDashboard']);
Route::get('/login', [DashboardController::class, 'viewLogin']);
