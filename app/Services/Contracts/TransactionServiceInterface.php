<?php

namespace App\Services\Contracts;

use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TransactionServiceInterface
{
    public function getUserTransactions(int $userId, array $params): LengthAwarePaginator;
    public function withdraw(int $userId , int $accountId , float $amount , string $name): Transaction;
    public function deposit(int $userId , int $accountId , float $amount , string $name): Transaction;
    public function transfer(int $userId, int $fromAccountId, string $toAccountNumber, float $amount , string $name): Transaction;
    public function generateAccountTransactionsReport(int $userId , int $accountId , string $fileType , string $period): array;

}
