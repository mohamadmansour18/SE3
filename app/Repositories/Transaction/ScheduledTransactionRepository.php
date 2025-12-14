<?php

namespace App\Repositories\Transaction;

use App\Enums\ScheduledTransactionStatus;
use App\Models\ScheduledTransaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class ScheduledTransactionRepository
{
    public function createTransaction(int $accountId , string $type, string $name , string $amount, Carbon $scheduledAt): ScheduledTransaction|Builder
    {
        return ScheduledTransaction::query()->create([
            'account_id'   => $accountId,
            'name'         => $name,
            'type'         => $type,
            'amount'       => $amount,
            'scheduled_at' => $scheduledAt,
            'status'       => ScheduledTransactionStatus::PENDING->value,
        ]);
    }
}
