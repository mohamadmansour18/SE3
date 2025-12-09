<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum TransactionStatus: string
{
    use EnumToArray;

    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
}
