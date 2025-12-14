<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum ScheduledTransactionStatus: string
{
    use EnumToArray;

    case PENDING = 'pending';
    case EXECUTED = 'executed';
    case CANCELLED = 'cancelled';
}
