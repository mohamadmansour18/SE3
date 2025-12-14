<?php

namespace App\Repositories\Transaction;

use App\Enums\TransactionStatus;
use App\Enums\TransactionType;
use App\Models\Account;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TransactionRepository
{
    public function getUserTransactions(int $userId , int $perPage , int $page)
    {
        $cacheKey = "user:{$userId}:transactions:p{$page}:pp{$perPage}";

        return Cache::tags(["user:{$userId}" , 'transaction'])->remember($cacheKey , now()->addMinutes(15) , function () use ($userId, $perPage, $page) {
            $accountIds = Account::query()
                ->where('user_id', $userId)
                ->pluck('id');

            return Transaction::query()
                ->where(function ($query) use ($accountIds) {
                    $query->whereIn('from_account_id', $accountIds)
                        ->orWhereIn('to_account_id', $accountIds);
                })
                ->with(['fromAccount:id,account_number', 'toAccount:id,account_number'])
                ->orderByDesc('created_at')
                ->paginate($perPage , ['*'] , 'page' , $page);
        });
    }

    public function createWithdraw(Account $account , int $userId , float $amount , string $name): Model|Builder
    {
        return Transaction::query()->create([
            'type'                => TransactionType::WITHDRAW->value,
            'name'                => $name,
            'from_account_id'     => $account->id ,
            'to_account_id'       => null ,
            'amount'              => $amount,
            'status'              => TransactionStatus::COMPLETED->value,
            'performed_by_user_id'=> $userId,
            'executed_at'         => now(),
        ]);
    }

    public function createDeposit(Account $account , int $userId , float $amount , string $name): Model|Builder
    {
        return Transaction::query()->create([
            'type'                => TransactionType::DEPOSIT->value,
            'name'                => $name ,
            'from_account_id'     => null ,
            'to_account_id'       => $account->id ,
            'amount'              => $amount,
            'status'              => TransactionStatus::COMPLETED->value,
            'performed_by_user_id'=> $userId,
            'executed_at'         => now(),
        ]);
    }

    public function createTransfer(Account $fromAccount, Account $toAccount, int $userId, float $amount , string $name): Model|Builder
    {
        $transaction = Transaction::query()->create([
            'type'                 => TransactionType::TRANSFER->value,
            'name'                 => $name,
            'from_account_id'      => $fromAccount->id,
            'to_account_id'        => $toAccount->id,
            'amount'               => $amount,
            'status'               => TransactionStatus::COMPLETED->value,
            'performed_by_user_id' => $userId,
            'executed_at'          => now(),
        ]);

        return $transaction->load(['fromAccount:id,user_id', 'toAccount:id,user_id']);
    }

    public function getAccountTransactionsForExport(int $accountId , Carbon $fromDate , Carbon $toDate): Collection|array
    {
        return Transaction::query()
            ->where(function($query) use ($accountId){
                $query->where('from_account_id', $accountId)
                      ->orWhere('to_account_id', $accountId);
            })
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->with(['fromAccount', 'toAccount'])
            ->orderBy('created_at')
            ->get();
    }
}
