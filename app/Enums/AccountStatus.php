<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum AccountStatus: string
{
    use EnumToArray;

    case ACTIVE = 'active';
    case FROZEN = 'frozen';
    case SUSPENDED = 'suspended';
    case CLOSED = 'closed';
}
