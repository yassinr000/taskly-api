<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Tous les jours à 8 h : rappel des tâches à rendre demain.
Schedule::command('taskly:send-reminders')->dailyAt('08:00');
