<?php

namespace App\Console;

use App\Console\Commands\MakeEnumCommand;
use App\Console\Commands\MakeTraitCommand;
use App\Console\Commands\RunBackupWithLog;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        MakeEnumCommand::class,
        MakeTraitCommand::class,
        RunBackupWithLog::class
    ];
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('backup:runWithLog');

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
