<?php

namespace App\Services\Proxy;

use App\Models\Transaction;
use App\Services\Contracts\AccountServiceInterface;
use App\Services\Contracts\TransactionServiceInterface;
use App\Traits\AroundTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;

class TransactionServiceProxy implements TransactionServiceInterface
{
    use AroundTrait;

    public function __construct(
        protected TransactionServiceInterface $inner
    ){}

    public function getUserTransactions(int $userId, array $params): LengthAwarePaginator
    {
        return $this->around(
            callback: fn() => $this->inner->getUserTransactions($userId, $params),
            audit: function (LengthAwarePaginator $paginator) use ($userId) {
                return [
                    'actor_id'     => $userId,
                    'subject_type' => Transaction::class,
                    'subject_id'   => null,
                    'changes'      => [
                        'action'       => 'view_transactions_history',
                        'current_page' => $paginator->currentPage(),
                        'per_page'     => $paginator->perPage(),
                        'total'        => $paginator->total(),
                    ],
                ];
            }
        );
    }

    public function withdraw(int $userId, int $accountId, float $amount, string $name): Transaction
    {
        return $this->around(
            callback:
                fn () => $this->inner->withdraw($userId, $accountId, $amount , $name),
            after:
                fn() => Cache::tags(["user:{$userId}", 'transactions'])->flush(),
            audit:
                function (Transaction $transaction) use ($userId) {
                    return [
                        'actor_id'     => $userId,
                        'subject_type' => Transaction::class,
                        'subject_id'   => $transaction->id,
                        'changes'      => [
                            'action'      => 'withdraw',
                            'account_id'  => $transaction->from_account_id,
                            'amount'      => $transaction->amount,
                            'executed_at' => $transaction->created_at->toDateTimeString(),
                        ],
                    ];
            },
        );
    }

    public function deposit(int $userId, int $accountId, float $amount, string $name): Transaction
    {
        return $this->around(
            callback:
                fn () => $this->inner->deposit($userId, $accountId, $amount , $name),
            after:
                fn() => Cache::tags(["user:{$userId}", 'transactions'])->flush(),
            audit:
                function (Transaction $transaction) use ($userId) {
                    return [
                        'actor_id'     => $userId,
                        'subject_type' => Transaction::class,
                        'subject_id'   => $transaction->id,
                        'changes'      => [
                            'action'      => 'deposit',
                            'account_id'  => $transaction->to_account_id,
                            'amount'      => $transaction->amount,
                            'executed_at' => $transaction->created_at->toDateTimeString(),
                        ],
                    ];
                },
        );
    }

    public function transfer(int $userId, int $fromAccountId, string $toAccountNumber, float $amount, string $name): Transaction
    {
        return $this->around(
            callback:
                fn () => $this->inner->transfer($userId, $fromAccountId, $toAccountNumber , $amount , $name),
            after:
                function (Transaction $transaction) use ($userId) {
                   Cache::tags(["user:{$userId}", 'transactions'])->flush();
                   Cache::tags(["user:{$transaction->toAccount->user_id}", 'transactions'])->flush();
                },
            audit:
            function (Transaction $transaction) use ($userId) {
                return [
                    'actor_id'     => $userId,
                    'subject_type' => Transaction::class,
                    'subject_id'   => $transaction->id,
                    'changes'      => [
                        'action'      => 'deposit',
                        'account_id'  => $transaction->to_account_id,
                        'amount'      => $transaction->amount,
                        'executed_at' => $transaction->created_at->toDateTimeString(),
                    ],
                ];
            },
        );

    }

    public function generateAccountTransactionsReport(int $userId, int $accountId, string $fileType, string $period): array
    {
        return $this->around(
            callback:
                fn () => $this->inner->generateAccountTransactionsReport($userId, $accountId, $fileType , $period),
            audit:
                function (array $transaction) use ($userId) {
                    return [
                        'actor_id'     => $userId,
                        'subject_type' => Transaction::class,
                        'subject_id'   => null,
                        'changes'      => [
                            'action'      => 'download_transactions_history',
                            'download_name' => $transaction['download_name']
                        ],
                    ];
                },
        );
    }
}
