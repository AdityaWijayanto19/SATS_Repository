<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Auto-mark devices as offline jika tidak mengirim data dalam 5 detik
Schedule::command('devices:mark-stale-offline --timeout=5')->everyFiveSeconds();
