<?php

namespace App\Console\Commands;

use App\Models\Backup;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class RunBackupWithLog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backup:runWithLog';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the backup and log the result in the backup_logs table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $runAt = now();

        try {

            Artisan::call('backup:run');

            Backup::create([
                'run_at' => $runAt,
                'status' => 'success',
                'message' => 'Backup completed successfully.',
            ]);

            Log::channel('aspect')->info('Backup finished successfully and has been logged.');

            return self::SUCCESS;

        } catch (\Throwable $e) {
            // في حال فشل عملية النسخ، نسجّل ذلك برضه
            Backup::create([
                'run_at' => $runAt,
                'status' => 'failed',
                'message' => $e->getMessage(),
            ]);

            Log::channel('aspect')->error('Backup failed: ' . $e->getMessage());

            return self::FAILURE;
        }
    }
}
