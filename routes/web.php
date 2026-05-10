<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Redis;

Route::get('/', function () {
    return view('welcome');
});


Route::get('/cek-redis', function () {
    try {
        Redis::set('cek_koneksi', 'Redis Berhasil Terkoneksi!');
        return Redis::get('cek_koneksi');
    } catch (\Exception $e) {
        return "Redis Eror: " . $e->getMessage();
    }
});


Route::get('/login', [AuthController::class, 'viewLoginPage'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.process');

Route::get('/forgot-password', [AuthController::class, 'showForgotPassword'])->name('password.forgot');
Route::post('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.email');

Route::get('/reset-password', [AuthController::class, 'showResetPassword'])
    ->name('password.reset')
    ->middleware('signed');

Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');


Route::middleware(['auth'])->group(function () {


    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ...
});
