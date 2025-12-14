<?php

namespace App\Repositories\Account;

use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AccountRepository
{
    public function findUserAccountById(int $userId , int $accountId): Account|Builder|null
    {
        return Account::query()
            ->where('id' , $accountId)
            ->where('user_id' , $userId)
            ->first();
    }

    public function decrementBalance(Account $account , float $amount): void
    {
        $account->balance = $account->balance - $amount;
        $account->save();
    }

    public function incrementBalance(Account $account , float $amount): void
    {
        $account->balance = $account->balance + $amount;
        $account->save();
    }

    public function findByAccountNumber(string $accountNumber): null|Account|Builder
    {
        return Account::query()
            ->where('account_number' , $accountNumber)
            ->first();
    }
}
