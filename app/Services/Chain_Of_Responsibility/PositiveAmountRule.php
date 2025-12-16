<?php

namespace App\Services\Chain_Of_Responsibility;

use App\Exceptions\ApiException;

class PositiveAmountRule extends AbstractTransactionRule
{
    protected function doCheck(TransactionContext $context): void
    {
        if ($context->amount <= 0) {
            throw new ApiException('يجب أن يكون المبلغ أكبر من صفر', 422);
        }
    }
}
