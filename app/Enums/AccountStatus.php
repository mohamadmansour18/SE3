<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum AccountStatus: string
{
    use EnumToArray;

    case ACTIVE = 'نشط';
    case FROZEN = 'مجمد';
    case SUSPENDED = 'موقوف';
    case CLOSED = 'مغلق';
}
