<?php

namespace App\Services\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionServiceInterface
{
    public function getUserTransactions(int $userId, array $params): LengthAwarePaginator;
}
