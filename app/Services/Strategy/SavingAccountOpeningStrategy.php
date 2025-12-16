<?php

namespace App\Services\Strategy;

use App\Enums\AccountStatus;
use App\Exceptions\ApiException;

class SavingAccountOpeningStrategy implements AccountOpeningStrategy
{
    public function prepare(int $userId, string $accountType, string $name, string $description, float $initialAmount): AccountOpeningResult
    {
        if ($initialAmount < 100) {
            throw new ApiException('الحد الأدنى لفتح حساب التوفير هو 100', 422);
        }

        return new AccountOpeningResult(
            initialBalance: $initialAmount,
            initialStatus: AccountStatus::ACTIVE->value,
        );
    }
}
