<?php

namespace App\Console\Commands;

use App\Enums\ScheduledTransactionStatus;
use App\Events\NotificationRequested;
use App\Models\ScheduledTransaction;
use App\Services\TransactionService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunScheduledTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:run-scheduled {--dry-run : Preview only without executing}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Execute pending scheduled transactions whose scheduled time has arrived';

    /**
     * Execute the console command.
     */

    public function __construct(
        private readonly TransactionService $transactionService
    )
    {
        parent::__construct();
    }

    public function handle()
    {
        $now = Carbon::now();

        $this->info('Running scheduled transactions up to: ' . $now->toDateTimeString());

        $query = ScheduledTransaction::query()
            ->where('status' , ScheduledTransactionStatus::PENDING->value)
            ->where('scheduled_at' , '<=' , $now)
            ->with('fromAccount');

        $total = $query->count();

        if($total === 0)
        {
            $this->info('No scheduled transactions to run');
            return Command::SUCCESS;
        }

        $this->info("Found {$total} scheduled transaction(s)");

        $dryRun = $this->option('dry-run');

        $query->orderBy('scheduled_at')->chunkById(10 , function($scheduledTransactions) use ($dryRun){
            foreach ($scheduledTransactions as $scheduled)
            {
                $account = $scheduled->fromAccount;

                if (!$account) {
                    Log::warning('Scheduled transaction has no account', [
                        'scheduled_transaction_id' => $scheduled->id,
                    ]);

                    $scheduled->status = 'cancelled';
                    $scheduled->save();

                    continue;
                }

                $userId    = $account->user_id;
                $accountId = $account->id;
                $amount    = (float) $scheduled->amount;
                $type      = $scheduled->type;
                $name      = $scheduled->name;

                $this->info("Processing scheduled #{$scheduled->id} ({$type}) for account {$account->account_number}, amount = {$amount}");

                if ($dryRun) {
                    continue;
                }

                try {
                    if($type === 'سحب')
                    {
                        $this->transactionService->withdraw($userId , $accountId , $amount , $name);
                    }elseif ($type === 'ايداع'){
                        $this->transactionService->deposit($userId , $accountId , $amount , $name);
                    }else{
                        Log::warning('Unknown scheduled transaction type', [
                            'scheduled_transaction_id' => $scheduled->id,
                            'type'                     => $type,
                        ]);
                        $scheduled->status = 'cancelled';
                        $scheduled->save();
                        continue;
                    }
                    $scheduled->status = ScheduledTransactionStatus::EXECUTED->value;
                    $scheduled->save();

                    NotificationRequested::dispatch([$userId] , "نجاح عملية جدولة" , "تم تنفيذ عملية ال {$type} المجدولة والخاصة بك بنجاح");

                    $this->info("Scheduled transaction #{$scheduled->id} executed successfully");
                    Log::channel('aspect')->info("Scheduled transaction #{$scheduled->id} executed successfully");

                }catch (\Throwable $throwable){
                    Log::error('Failed to execute scheduled transaction', [
                        'scheduled_transaction_id' => $scheduled->id,
                        'type'                     => $type,
                        'message'                  => $throwable->getMessage(),
                        'trace'                    => $throwable->getTraceAsString(),
                    ]);

                    $scheduled->status = 'cancelled';
                    $scheduled->save();

                    $this->error("Failed to execute scheduled transaction #{$scheduled->id}: {$throwable->getMessage()}");
                }
            }
        });

        $this->info('Done running scheduled transactions');

        return Command::SUCCESS;
    }
}
