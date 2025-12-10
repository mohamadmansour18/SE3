<?php

namespace App\Services;

use App\Helpers\TextHelper;
use App\Models\Transaction;
use App\Repositories\Transaction\TransactionRepository;
use App\Services\Contracts\TransactionServiceInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TransactionService implements TransactionServiceInterface
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
    ) {}

    public function getUserTransactions(int $userId, array $params): LengthAwarePaginator
    {
        $paginator = $this->transactionRepository->getUserTransactions($userId, $params['per_page'] , $params['page']);

        $paginator->getCollection()->transform(function (Transaction $transaction) {
            return [
                'transaction_id'        => $transaction->id,
                'name'                  => $transaction->name,
                'from_account_number'   => $transaction->fromAccount?->account_number,
                'to_account_number'     => $transaction->toAccount?->account_number,
                'amount'                => $transaction->amount,
                'type'                  => $transaction->type,
                'executed_at'           => $transaction->created_at?->diffForHumans(),
            ];
        });

        return $paginator;
    }
}
