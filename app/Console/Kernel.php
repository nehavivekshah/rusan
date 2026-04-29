<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('todo:send-reminders')->everyMinute();
        $schedule->command('app:send-scheduled-emails')->dailyAt('09:00');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        // Load all commands in app/Console/Commands
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
