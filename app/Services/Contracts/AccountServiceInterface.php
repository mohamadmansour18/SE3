<?php

namespace App\Services\Contracts;

use App\Models\Account;
use Illuminate\Database\Eloquent\Collection;

interface AccountServiceInterface
{
    public function openAccount(int $userId, string $accountType, string $name , string $description , float $initialAmount): Account;
    public function getAccounts(int $userId): array|Collection;
    public function updateAccount(int $userId, int $accountId, array $attributes): Account;
}
