<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Exceptions\ApiException;
use App\Models\Account;
use App\Models\Transaction;
use App\Repositories\Account\AccountRepository;
use App\Repositories\Transaction\ScheduledTransactionRepository;
use App\Repositories\Transaction\TransactionRepository;
use App\Services\Adapter\CsvTransactionAdapter;
use App\Services\Adapter\PdfTransactionAdapter;
use App\Services\Contracts\TransactionServiceInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class TransactionService implements TransactionServiceInterface
{
    public function __construct(
        private readonly TransactionRepository $transactionRepository,
        private readonly AccountRepository $accountRepository,
        private readonly ScheduledTransactionRepository $scheduledTransactionRepository,
        private readonly PdfTransactionAdapter $pdfAdapter,
        private readonly CsvTransactionAdapter $csvAdapter,
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

    public function withdraw(int $userId, int $accountId, float $amount, string $name): Transaction
    {
        $account = $this->accountRepository->findUserAccountById($userId, $accountId);


        if($account->status !== AccountStatus::ACTIVE->value)
        {
            throw new ApiException("لا يمكن اجراء عملية سحب على حساب غير نشط" , 422);
        }

        if($account->balance < $amount)
        {
            throw new ApiException("ليس لديك رصيد كافي لاتمام عملية السحب" , 422);
        }

        return DB::transaction(function () use ($userId, $account, $amount, $name) {
            $this->accountRepository->decrementBalance($account, $amount);

            return $this->transactionRepository->createWithdraw($account , $userId , $amount , $name );
        });
    }

    public function deposit(int $userId, int $accountId, float $amount, string $name): Transaction
    {
        $account = $this->accountRepository->findUserAccountById($userId, $accountId);

        if($account->status !== AccountStatus::ACTIVE->value)
        {
            throw new ApiException("لا يمكن اجراء عملية ايداع على حساب غير نشط" , 422);
        }

        return DB::transaction(function () use ($userId, $account, $amount, $name) {
            $this->accountRepository->incrementBalance($account, $amount);

            return $this->transactionRepository->createDeposit($account , $userId , $amount , $name );
        });
    }

    public function transfer(int $userId, int $fromAccountId, string $toAccountNumber, float $amount, string $name): Transaction
    {
        $fromAccount = $this->accountRepository->findUserAccountById($userId, $fromAccountId);

        if($fromAccount->status !== AccountStatus::ACTIVE->value)
        {
            throw new ApiException("لا يمكن اجراء عملية تحويل على حساب غير نشط" , 422);
        }

        if($fromAccount->balance < $amount)
        {
            throw new ApiException("ليس لديك رصيد كافي لاتمام عملية التحويل" , 422);
        }

        $toAccount = $this->accountRepository->findByAccountNumber($toAccountNumber);

        if($toAccount->id === $fromAccount->id)
        {
            throw new ApiException("لايمكن التحويل الى نفس الحساب" , 422);
        }

        if($toAccount->status !== AccountStatus::ACTIVE->value)
        {
            throw new ApiException("لا يمكن اجراء عملية تحويل لحساب غير نشط" , 422);
        }

        return DB::transaction(function () use ($fromAccount , $toAccount , $userId , $amount, $name) {

            $this->accountRepository->decrementBalance($fromAccount , $amount);

            $this->accountRepository->incrementBalance($toAccount , $amount);

            return $this->transactionRepository->createTransfer($fromAccount , $toAccount , $userId , $amount , $name);
        });
    }

    public function generateAccountTransactionsReport(int $userId, int $accountId, string $fileType, string $period): array
    {
        $account = $this->accountRepository->findUserAccountById($userId, $accountId);

        [$fromDate, $toDate] = $this->resolvePeriod($period);

        $transactions = $this->transactionRepository->getAccountTransactionsForExport($accountId , $fromDate , $toDate);

        if($transactions->isEmpty())
        {
            throw new ApiException("لايوجد سجل معاملات مالية لهذه الفترة المحددة" , 422);
        }

        Storage::disk('public')->makeDirectory('stats');

        try {
            $exporter = match ($fileType)
            {
                'pdf' => $this->pdfAdapter,
                'csv' => $this->csvAdapter,

                default => throw new ApiException("نوع الملف غير صالح" , 422)
            };

            return $exporter->export($account, $transactions, $fromDate, $toDate);

        }catch(\Throwable $exception)
        {
            Log::error('PDF generation failed' , [
                'message' => $exception->getMessage(),
                'trace'   => $exception->getTraceAsString(),
            ]);
            throw new ApiException('! حدث خطأ غير متوقع اثناء التنفيذ', 500);
        }

    }

    private function resolvePeriod(string $period): array
    {
        $to = Carbon::now()->endOfDay();

        $from = match ($period) {
            'week' => Carbon::now()->subWeek()->startOfDay(),
            'month' => Carbon::now()->subMonth()->startOfDay(),
            'year' => Carbon::now()->subYear()->startOfDay(),
            default => throw new ApiException("فترة غير صحيحة" , 422),
        };

        return [$from , $to];
    }

    public function scheduleUserTransaction(int $userId, int $accountId, string $type, float $amount, string $scheduledAt, string $name): void
    {
        $account = $this->accountRepository->findUserAccountById($userId, $accountId);

        if ($account->status !== AccountStatus::ACTIVE->value) {
            throw new ApiException('لا يمكن جدولة عملية على حساب غير نشط', 422);
        }

        $scheduledAtCarbon = Carbon::parse($scheduledAt);

        if($scheduledAtCarbon->isPast())
        {
            throw new ApiException('وقت الجدولة يجب أن يكون في المستقبل', 422);
        }

        $now = Carbon::now();
        $maxDate = $now->copy()->addMonths(3);

        if ($scheduledAtCarbon->greaterThan($maxDate)) {
            throw new ApiException('وقت الجدولة لا يمكن أن يتجاوز 3 أشهر من تاريخ اليوم', 422);
        }

        if ($type === 'withdraw' && $account->balance < $amount) {
            throw new ApiException('ليس لديك رصيد كافٍ لجدولة عملية السحب هذه', 422);
        }

        $this->scheduledTransactionRepository->createTransaction($accountId , $type , $name , $amount , $scheduledAtCarbon);
    }
}
