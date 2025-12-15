<?php

namespace App\Services\Proxy;

use App\Models\Account;
use App\Services\AccountService;
use App\Services\Contracts\AccountServiceInterface;
use App\Traits\AroundTrait;
use Illuminate\Database\Eloquent\Collection;

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

    public function getAccounts(int $userId): array|Collection
    {
        return $this->around(
            callback: fn () => $this->inner->getAccounts($userId),
            audit: function () use ($userId) {
                return [
                    'actor_id'     => $userId,
                    'subject_type' => Account::class,
                    'subject_id'   => null,
                    'changes'      => [
                        'action'   => 'get.citizen.account',
                    ],
                ];
            }
        );
    }

    public function updateAccount(int $userId, int $accountId, array $attributes): Account
    {
        return $this->around(
            callback: fn () => $this->inner->updateAccount(
                $userId,
                $accountId,
                $attributes
            ),
            audit: function (Account $account) use ($userId, $attributes) {
                return [
                    'actor_id'     => $userId,
                    'subject_type' => Account::class,
                    'subject_id'   => $account->id,
                    'changes'      => array_merge(
                        ['action' => 'update_account'],
                        $attributes
                    ),
                ];
            },
        );
    }
}
