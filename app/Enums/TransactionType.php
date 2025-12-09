<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum TransactionType: string
{
    use EnumToArray;

    case DEPOSIT = 'deposit';
    case WITHDRAW = 'withdraw';
    case TRANSFER = 'transfer';
}
