<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

Schedule::command('posts:process-scheduled')
    ->everyMinute()
    ->withoutOverlapping()
    ->onFailure(function ($e) {
        Log::error('Failed to process scheduled posts', ['error' => $e]);
    });
