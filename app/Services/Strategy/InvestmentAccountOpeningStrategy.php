<?php

namespace App\Services\Strategy;

use App\Enums\AccountStatus;
use App\Exceptions\ApiException;

class InvestmentAccountOpeningStrategy implements AccountOpeningStrategy
{
    public function prepare(
        int $userId,
        string $accountType,
        string $name,
        string $description,
        float $initialAmount
    ): AccountOpeningResult {
        if ($initialAmount < 1000) {
            throw new ApiException('الحد الأدنى لفتح حساب استثماري هو 1000', 422);
        }

        return new AccountOpeningResult(
            initialBalance: $initialAmount,
            initialStatus: AccountStatus::ACTIVE->value,
        );
    }
}
