<?php

use App\Console\Commands\RemandPaymentDeadlines;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(RemandPaymentDeadlines::class)
    ->dailyAt('09:00')
    ->timezone(config('app.timezone'));

Schedule::command('backup:run')
    ->dailyAt('03:00')
    ->timezone(config('app.timezone'));

Schedule::command('backup:clean')
    ->dailyAt('04:00')
    ->timezone(config('app.timezone'));

Schedule::command('app:sync-remote-database')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->timezone(config('app.timezone'));
