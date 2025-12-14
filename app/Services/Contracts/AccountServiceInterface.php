<?php

namespace App\Services\Contracts;

use App\Models\Account;

interface AccountServiceInterface
{
    public function openAccount(int $userId, string $accountType, string $name , string $description , float $initialAmount): Account;
}
