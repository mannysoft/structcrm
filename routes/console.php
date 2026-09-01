<?php

use App\Console\Commands\SendDueMarketingEmails;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command(SendDueMarketingEmails::class)
    ->dailyAt('09:00')
    ->withoutOverlapping();
