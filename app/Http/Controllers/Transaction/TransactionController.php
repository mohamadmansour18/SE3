<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaginateRequest;
use App\Models\Transaction;
use App\Services\Contracts\TransactionServiceInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    use ApiResponse;

    public function __construct(
        private readonly TransactionServiceInterface $transactionService
    )
    {}

    public function getUserTransactions(PaginateRequest $request): JsonResponse
    {
        $userId = Auth::id();

        $paginator = $this->transactionService->getUserTransactions($userId , ['page' => $request->getPage() , 'per_page' => $request->getPerPage()]);

        return $this->paginatedResponse($paginator , "تم جلب البيانات بشكل جزئي بنجاح" , 200);
    }
}
