<?php

namespace App\Services\Chain_Of_Responsibility;

use App\Exceptions\ApiException;

class SufficientBalanceRule extends AbstractTransactionRule
{
    protected function doCheck(TransactionContext $context): void
    {
        if ($context->account->balance < $context->amount) {
            throw new ApiException('ليس لديك رصيد كافٍ لإتمام عملية السحب', 422);
        }
    }
}
