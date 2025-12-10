<?php

namespace App\Repositories\Transaction;

use App\Models\Account;
use App\Models\Transaction;
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
}
