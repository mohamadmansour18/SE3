<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\DepositRequest;
use App\Http\Requests\ExportTransactionsRequest;
use App\Http\Requests\PaginateRequest;
use App\Http\Requests\transferRequest;
use App\Http\Requests\WithdrawRequest;
use App\Models\Transaction;
use App\Services\Contracts\TransactionServiceInterface;
use App\Services\TransactionService;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TransactionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TransactionServiceInterface $transactionService,
    )
    {}

    public function getUserTransactions(PaginateRequest $request): JsonResponse
    {
        $userId = Auth::id();

        $paginator = $this->transactionService->getUserTransactions($userId , ['page' => $request->getPage() , 'per_page' => $request->getPerPage()]);

        return $this->paginatedResponse($paginator , "تم جلب البيانات بشكل جزئي بنجاح" , 200);
    }

    public function withdraw(WithdrawRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $data = $request->validated();

        $this->transactionService->withdraw($userId , $data['account_id'] , $data['amount'] , $data['name']);

        return $this->successResponse("تمت عملية سسحب المبلغ بنجاح عزيزي المواطن" , 200);
    }

    public function deposit(DepositRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $data = $request->validated();

        $this->transactionService->deposit($userId, $data['account_id'] , $data['amount'] , $data['name']);

        return $this->successResponse("تمت عملية ايداع المبلغ بنجاح عزيزي المواطن" ,200);
    }

    public function transfer(TransferRequest $request): JsonResponse
    {
        $userId = Auth::id();
        $data = $request->validated();

        $this->transactionService->transfer($userId, $data['account_id'] , $data['to_account_number'] , $data['amount'] , $data['name']);

        return $this->successResponse("تمت عملية تحويل المبلغ بنجاح عزيزي المواطن" ,200);
    }

    public function export(ExportTransactionsRequest $request): BinaryFileResponse
    {
        $userId = Auth::id();
        $data = $request->validated();

        $result = $this->transactionService->generateAccountTransactionsReport($userId , $data['account_id'] , $data['file_type'] , $data['period']);

        return response()->download($result['path'] , $result['download_name'])->deleteFileAfterSend(true);
    }
}
