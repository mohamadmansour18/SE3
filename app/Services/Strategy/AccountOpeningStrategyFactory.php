<?php

namespace App\Services\Strategy;

use App\Exceptions\ApiException;

class AccountOpeningStrategyFactory
{
    public function __construct(
        private readonly SavingAccountOpeningStrategy      $savingStrategy,
        private readonly CheckingAccountOpeningStrategy    $checkingStrategy,
        private readonly LoanAccountOpeningStrategy        $loanStrategy,
        private readonly InvestmentAccountOpeningStrategy  $investmentStrategy,
    ) {}

    public function forType(string $accountType): AccountOpeningStrategy
    {
        return match ($accountType) {
            'توفير'      => $this->savingStrategy,
            'جاري'       => $this->checkingStrategy,
            'قرض'        => $this->loanStrategy,
            'استثماري'   => $this->investmentStrategy,
            default      => throw new ApiException('نوع حساب غير مدعوم عند الفتح', 422),
        };
    }
}
