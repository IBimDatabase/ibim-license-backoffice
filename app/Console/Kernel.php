<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use App\Cron\ExpiredLicenseCron;
use App\Cron\WPProductCron;
use App\Cron\WPOrderCron;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->call(new ExpiredLicenseCron)->daily();
        // $schedule->call(new WPProductCron)->everyMinute();
        // $schedule->call(new WPOrderCron)->everyFiveMinutes();
        // $schedule->command('license-expire-notification')->hourly()->withoutOverlapping();
        $schedule->command('license-expire-notification')->monthlyOn(1, '08:00')->timezone('Australia/Sydney')->withoutOverlapping();
        // $schedule->command('license-expire-user-notification')->dailyAt('08:01')->timezone('Australia/Sydney')->withoutOverlapping();
        // $schedule->command('license-expire-user-notification')->everyTenMinutes()->timezone('Australia/Sydney')->withoutOverlapping();

    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
