<?php

namespace App\Services;

use App\Models\Account;
use App\Repositories\Account\AccountRepository;
use App\Services\Contracts\AccountServiceInterface;

class AccountService implements AccountServiceInterface
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
    )
    {}

    public function openAccount(int $userId, string $accountType, string $name, string $description, float $initialAmount): Account
    {
        return $this->accountRepository->createAccount($userId, $accountType, $name, $description, $initialAmount);
    }
}
