<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// Jalankan otomatisasi generate status ALPA tiap hari jam 23:55 malam
Schedule::command('absensi:generate-alpha')->dailyAt('23:55');
