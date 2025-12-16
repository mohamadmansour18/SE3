<?php

namespace App\Repositories\Account;

use App\Enums\AccountStatus;
use App\Models\Account;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
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

    public function createAccount(int $userId , string $accountType , string $name , string $description , float $amount , string $status): Account
    {

        return DB::transaction(function () use ($userId, $accountType, $name, $description , $amount , $status) {
           $lastNumber = Account::withTrashed()->lockForUpdate()->max('account_number');
           $nextNumber = $lastNumber ? $lastNumber + 1 : 1;

           $account = Account::query()->create([
               'user_id'        => $userId,
               'type'           => $accountType,
               'name'           => $name,
               'description'    => $description,
               'account_number' => $nextNumber,
               'status'         => $status,
               'balance'        => $amount ,
           ]);

           return $account->fresh();
        });
    }

    public function findAccountByUserId(int $userId): Collection|array
    {
        return Account::query()
            ->where('user_id' , $userId)
            ->get([ 'id' , 'name' , 'account_number' , 'description' , 'type' , 'status' , 'balance' , 'created_at']);
    }

    public function updateAccountFields(Account $account, array $attributes): Account
    {
        foreach (['name', 'description', 'status'] as $field) {
            if (array_key_exists($field, $attributes)) {
                $account->{$field} = $attributes[$field];
            }
        }

        $account->save();

        return $account;
    }
}
