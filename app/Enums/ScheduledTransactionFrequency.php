<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum ScheduledTransactionFrequency: string
{
    use EnumToArray;

    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
}
