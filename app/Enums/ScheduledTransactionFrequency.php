<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum ScheduledTransactionFrequency: string
{
    use EnumToArray;

    case DAILY = 'يومي';
    case WEEKLY = 'اسبوعي';
    case MONTHLY = 'شهري';
}
