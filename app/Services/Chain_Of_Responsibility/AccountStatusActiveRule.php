<?php

namespace App\Services\Chain_Of_Responsibility;

use App\Enums\AccountStatus;
use App\Exceptions\ApiException;

class AccountStatusActiveRule extends AbstractTransactionRule
{
    protected function doCheck(TransactionContext $context): void
    {
        if ($context->account->status !== AccountStatus::ACTIVE->value) {
            $op = $context->operationType === 'withdraw' ? 'سحب' : 'إيداع';
            throw new ApiException("لا يمكن إجراء عملية {$op} على حساب غير نشط", 422);
        }
    }
}
