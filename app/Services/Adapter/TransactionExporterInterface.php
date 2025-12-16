<?php

namespace App\Services\Adapter;

use App\Models\Account;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

interface TransactionExporterInterface
{
    public function export(Account $account, Collection $transactions, Carbon $fromDate, Carbon $toDate): array;
}
