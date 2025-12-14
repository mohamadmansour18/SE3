<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\ScheduleTransactionRequest;
use App\Models\User;
use App\Services\TransactionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ScheduledTransactionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TransactionService $transactionService
    )
    {}

    public function schedule(ScheduleTransactionRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $data = $request->validated();

        $this->transactionService->scheduleUserTransaction($userId , $data['account_id'] , $data['type'] , $data['amount'] , $data['scheduled_at'] , $data['name']);
        return $this->successResponse("تمت عملية الجدولة الخاصة بك بنجاح" , 200);
    }
}
