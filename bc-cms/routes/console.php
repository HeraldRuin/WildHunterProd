<?php

use Illuminate\Console\Scheduling\Schedule;

app()->booted(function () {
    $schedule = app(Schedule::class);
    $schedule->command('payments:process')->everyMinute()->timezone('Europe/Moscow');
});
