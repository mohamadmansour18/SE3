<?php

namespace App\Services\Chain_Of_Responsibility;

use App\Models\Account;

class TransactionContext
{
    public function __construct(
        public int     $userId,
        public Account $account,
        public float   $amount,
        public string  $operationName,
        public string  $operationType,
    ) {}
}
