<?php

namespace App\Services\Strategy;

use App\Enums\AccountStatus;
use App\Exceptions\ApiException;

class LoanAccountOpeningStrategy
{
    public function prepare(
        int $userId,
        string $accountType,
        string $name,
        string $description,
        float $initialAmount
    ): AccountOpeningResult {
        if ($initialAmount != 0.0) {
            throw new ApiException('يتم فتح حساب القرض برصيد ابتدائي 0، ويتم إضافة مبلغ القرض لاحقاً.', 422);
        }

        return new AccountOpeningResult(
            initialBalance: 0.0,
            initialStatus: AccountStatus::FROZEN->value,
        );
    }
}
