<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum ScheduledTransactionStatus: string
{
    use EnumToArray;

    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case CANCELLED = 'cancelled';
}
