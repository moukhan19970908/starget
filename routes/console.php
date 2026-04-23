<?php

use App\Models\Contract;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Daily job: mark contracts expiring within 30 days
Schedule::call(function () {
    Contract::where('status', 'active')
        ->whereNotNull('expires_at')
        ->where('expires_at', '<=', now()->addDays(30))
        ->update(['status' => 'expiring']);
})->daily()->name('contracts:check-expiry');
