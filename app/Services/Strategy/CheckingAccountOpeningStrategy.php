<?php

namespace App\Services\Strategy;

use App\Enums\AccountStatus;
use App\Exceptions\ApiException;

class CheckingAccountOpeningStrategy implements AccountOpeningStrategy
{
    public function prepare(int $userId, string $accountType, string $name, string $description, float $initialAmount): AccountOpeningResult
    {
        if ($initialAmount < 0)
        {
            throw new ApiException("لايمكن فتح حساب جاري بمبلغ سالب" , 422);
        }

        return new AccountOpeningResult(
            initialBalance: $initialAmount,
            initialStatus: AccountStatus::ACTIVE->value,
        );
    }
}
