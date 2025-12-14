<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Exceptions\ApiException;
use App\Helpers\TextHelper;
use App\Models\Account;
use App\Models\Transaction;
use App\Repositories\Account\AccountRepository;
use App\Repositories\Transaction\TransactionRepository;
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
            return $fileType === 'pdf'
                ?  $this->generatePdf($account, $transactions, $fromDate, $toDate)
                :  $this->generateCsv($account, $transactions, $fromDate, $toDate);
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

    private function generatePdf(Account $account, $transactions, Carbon $fromDate, Carbon $toDate): array
    {
        $fileName = sprintf(
            'transactions_%s_%s_%s.pdf',
            $account->account_number ,
            $fromDate->format('Ymd'),
            $toDate->format('Ymd')
        );

        $relativePath = 'stats/' . $fileName;
        $fullPath = Storage::disk('public')->path($relativePath);

        $mpdfTemp = storage_path('app/mpdf-temp');
        if (!File::exists($mpdfTemp)) {
            File::makeDirectory($mpdfTemp, 0755, true);
        }

        $mpdf = new Mpdf([
            'mode'              => 'utf-8',
            'format'            => 'A4',
            'directionality'    => 'rtl',
            'autoLangToFont'    => true,
            'autoScriptToLang'  => true,
            'tempDir'           => $mpdfTemp,
            'margin_top'        => 15,
            'margin_bottom'     => 15,
            'margin_left'       => 10,
            'margin_right'      => 10,
        ]);

        $html = view('reports.transaction' , [
            'account'      => $account,
            'transactions' => $transactions,
            'fromDate'     => $fromDate,
            'toDate'       => $toDate,
        ])->render();

        $mpdf->WriteHTML($html);
        $mpdf->Output($fullPath , Destination::FILE);

        return [
            'path' => $fullPath,
            'download_name' => $fileName,
        ];
    }

    private function generateCsv(Account $account, $transactions, Carbon $fromDate, Carbon $toDate): array
    {
        $fileName = sprintf(
            'transactions_%s_%s_%s.csv',
            $account->account_number,
            $fromDate->format('Ymd'),
            $toDate->format('Ymd')
        );

        $relativePath = 'stats/' . $fileName;
        $fullPath     = Storage::disk('public')->path($relativePath);

        $handle = fopen($fullPath, 'w');

        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

        fputcsv($handle, [
            'رقم المعاملة',
            'نوع العملية',
            'المبلغ',
            'رقم حساب المصدر',
            'رقم حساب الوجهة',
            'تاريخ التنفيذ',
        ]);

        foreach ($transactions as $transaction) {
            fputcsv($handle, [
                $transaction->id,
                $this->translateType($transaction->type),
                $transaction->amount,
                $transaction->fromAccount?->account_number,
                $transaction->toAccount?->account_number,
                $transaction->created_at->format('Y-m-d H:i'),
            ]);
        }

        fclose($handle);

        return [
            'path'          => $fullPath,
            'download_name' => $fileName,
        ];
    }

    private function translateType(string $type): string
    {
        return match ($type) {
            'deposit'  => 'إيداع',
            'withdraw' => 'سحب',
            'transfer' => 'تحويل',
            default    => $type,
        };
    }
}
