<?php

namespace App\Repositories\Account;

use App\Enums\AccountStatus;
use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

    public function createAccount(int $userId , string $accountType , string $name , string $description , float $amount): Account
    {

        return DB::transaction(function () use ($userId, $accountType, $name, $description , $amount) {
           $lastNumber = Account::withTrashed()->lockForUpdate()->max('account_number');
           $nextNumber = $lastNumber ? $lastNumber + 1 : 1;

           $account = Account::query()->create([
               'user_id'        => $userId,
               'type'           => $accountType,
               'name'           => $name,
               'description'    => $description,
               'account_number' => $nextNumber,
               'status'         => AccountStatus::ACTIVE->value,
               'balance'        => $amount ,
           ]);

           return $account->fresh();
        });
    }
}
