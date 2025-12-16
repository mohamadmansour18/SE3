<?php

namespace App\Services\Adapter;

use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

class PdfTransactionAdapter implements TransactionExporterInterface
{
    public function export(Account $account, Collection $transactions, Carbon $fromDate, Carbon $toDate): array
    {
        $fileName = sprintf(
            'transactions_%s_%s_%s.pdf',
            $account->account_number,
            $fromDate->format('Ymd'),
            $toDate->format('Ymd')
        );

        $relativePath = 'stats/' . $fileName;
        $fullPath     = Storage::disk('public')->path($relativePath);

        // mpdf temp dir
        $mpdfTemp = storage_path('app/mpdf-temp');
        if (! File::exists($mpdfTemp)) {
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

        $html = view('reports.transaction', [
            'account'      => $account,
            'transactions' => $transactions,
            'fromDate'     => $fromDate,
            'toDate'       => $toDate,
        ])->render();

        $mpdf->WriteHTML($html);
        $mpdf->Output($fullPath, Destination::FILE);

        return [
            'path'          => $fullPath,
            'download_name' => $fileName,
        ];
    }
}
