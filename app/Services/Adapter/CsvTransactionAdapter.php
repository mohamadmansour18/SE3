<?php

namespace App\Services\Adapter;

use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class CsvTransactionAdapter implements TransactionExporterInterface
{
    public function export(Account $account, Collection $transactions, Carbon $fromDate, Carbon $toDate): array
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

        // Header
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
