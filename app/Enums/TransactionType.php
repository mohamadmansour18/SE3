<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum TransactionType: string
{
    use EnumToArray;

    case DEPOSIT = 'ياداع';
    case WITHDRAW = 'سحب';
    case TRANSFER = 'تحويل';
}
