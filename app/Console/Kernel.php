<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->command('test-cron-job')->everyMinute();
        $schedule->command('media:prune')->dailyAt('02:00');
        // $schedule->command('activitylog:clean')->dailyAt('01:30');
        $schedule->command('reminder:send')->dailyAt('05:00');
        $schedule->command('backup:run')->daily();
        $schedule->command('backup:clean')->dailyAt('01:00');
        // $schedule->command('ccavenue:status')->everyFiveMinutes();
        // $schedule->command('billdesk:status')->everyFiveMinutes();
        $schedule->command('student:update-service-allocation')->dailyAt('01:00');

        if (config('saas.tenancy.enabled', false)) {
            // Queue dispatch is the primary path; this command recovers runs
            // created while the broker was unavailable.
            $schedule->command('saas:provision --pending')
                ->everyFiveMinutes()
                ->withoutOverlapping(15)
                ->onOneServer();

            $schedule->command('saas:prune-demo-requests')
                ->dailyAt('03:15')
                ->withoutOverlapping(30)
                ->onOneServer();

            if (config('saas.billing.provider', 'null') !== 'null') {
                $schedule->command('saas:reconcile-subscriptions')

                    ->cron((string) config('saas.billing.reconcile_schedule', '0 */6 * * *'))
                    ->withoutOverlapping(30)
                    ->onOneServer();
            }
        }
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
