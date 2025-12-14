<?php

namespace App\Services\Proxy;

use App\Models\Account;
use App\Services\AccountService;
use App\Services\Contracts\AccountServiceInterface;
use App\Traits\AroundTrait;

class AccountServiceProxy implements AccountServiceInterface
{
    use AroundTrait;
    public function __construct(
        protected AccountServiceInterface $inner
    ){}

    public function openAccount(int $userId, string $accountType, string $name, string $description, float $initialAmount): Account
    {
        return $this->around(

            callback: fn () => $this->inner->openAccount($userId, $accountType, $name , $description , $initialAmount),

            audit: function (Account $account) use ($userId, $accountType, $initialAmount) {
                return [
                    'actor_id'     => $userId,
                    'subject_type' => Account::class,
                    'subject_id'   => $account->id,
                    'changes'      => [
                        'action'         => 'open_account',
                        'account_number' => $account->account_number,
                        'account_type'   => $accountType,
                        'initial_amount' => $initialAmount,
                    ],
                ];
            },
        );
    }
}
