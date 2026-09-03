<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// SDM & Raport — penjadwalan harian/bulanan (PLAN §4)
Schedule::command('contracts:send-expiry-alerts')->dailyAt('07:00');
Schedule::command('discipline:auto-detect-attendance')->dailyAt('23:00');
Schedule::command('discipline:check-point-thresholds')->dailyAt('23:30');
Schedule::command('discipline:apply-clean-record-bonus')->monthlyOn(1, '01:00');
// reports:generate-semester — manual + awal semester, tidak dijadwalkan otomatis
