<?php

namespace App\Console;

use App\Http\Controllers\HomeController;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Schedule the task to run at 00:01 and 02:00
        $schedule->call(function () {
            app(HomeController::class)->calcOpenedBoxForToday();
        })->cron('5 0 * * *'); // Runs at 00:18

        // $schedule->call(function () {
        //     app(HomeController::class)->calcOpenedBoxForToday();
        // })->cron('0 18 * * *'); // Runs at 18:00
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
