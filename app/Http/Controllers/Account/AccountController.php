<?php

namespace App\Http\Controllers\Account;

use App\Enums\AccountStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\OpenAccountRequest;
use App\Http\Requests\UpdateAccountRequest;
use App\Models\Account;
use App\Services\Contracts\AccountServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly AccountServiceInterface $accountService,
    ) {}

    public function openAccount(OpenAccountRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $data = $request->validated();

        $this->accountService->openAccount($userId, $data['account_type'] , $data['name'] , $data['description'] , $data['initial_amount']);

        return  $this->successResponse("عزيز المستخدم تمت عملية انشاء الحساب بنجاح" , 201);
    }

    public function getAccounts(): JsonResponse
    {
        $userId = Auth::id();

        $data = $this->accountService->getAccounts($userId);

        return $this->dataResponse($data , 200);
    }

    public function update(UpdateAccountRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $accountId = $request->getAccountId();
        $attributes = $request->getUpdateData();

        $this->accountService->updateAccount($userId, $accountId, $attributes);

        return $this->successResponse("تم تعديل بياناتك بنجاح عزيزي المستخدم" , 200);
    }

    public function getAccountForSelect(): JsonResponse
    {
        $userId = Auth::id();
        $userAccounts = Account::query()
            ->where('user_id' , $userId)
            ->where('status' , AccountStatus::ACTIVE->value)
            ->select('id' , 'name')->get();
        return $this->dataResponse($userAccounts , 200);
    }
}
