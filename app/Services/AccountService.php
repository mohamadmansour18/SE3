<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Exceptions\ApiException;
use App\Models\Account;
use App\Repositories\Account\AccountRepository;
use App\Services\Contracts\AccountServiceInterface;
use Illuminate\Database\Eloquent\Collection;

class AccountService implements AccountServiceInterface
{
    public function __construct(
        private readonly AccountRepository $accountRepository,
    )
    {}

    public function openAccount(int $userId, string $accountType, string $name, string $description, float $initialAmount): Account
    {
        return $this->accountRepository->createAccount($userId, $accountType, $name, $description, $initialAmount);
    }

    public function getAccounts(int $userId): array|Collection
    {
        $accounts = $this->accountRepository->findAccountByUserId($userId);

        $newMap = $accounts->map(function (Account $account) {
            return [
                'id' => $account->id ,
                'name' => $account->name,
                'account_number' => $account->account_number,
                'description' => $account->description,
                'type' => $account->type,
                'status' => $account->status,
                'balance' => $account->balance,
                'created_at' => $account->created_at->format('Y-m-d'),
            ];
        })->values()->toArray();

        return $newMap;
    }

    public function updateAccount(int $userId, int $accountId, array $attributes): Account
    {
        $account = $this->accountRepository->findUserAccountById($userId, $accountId);

        if($account->status === AccountStatus::CLOSED->value)
        {
            throw new ApiException("لا يمكن اجراء تعديلات على حساب مغلق" , 422);

        }
        return $this->accountRepository->updateAccountFields($account , $attributes);
    }
}
