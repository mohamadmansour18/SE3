<?php

namespace App\Services\Proxy;

use App\Models\Transaction;
use App\Services\Contracts\AccountServiceInterface;
use App\Services\Contracts\TransactionServiceInterface;
use App\Traits\AroundTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

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
}
