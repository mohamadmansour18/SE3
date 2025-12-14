<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum TransactionType: string
{
    use EnumToArray;

    case DEPOSIT = 'ايداع';
    case WITHDRAW = 'سحب';
    case TRANSFER = 'تحويل';
}
