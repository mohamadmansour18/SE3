<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum UserRole: string
{
    use EnumToArray;

    case CITIZEN = 'citizen';
    case TELLER  = 'Teller';
    case MANAGER   = 'manager';
}
