<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\OpenAccountRequest;
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
}
