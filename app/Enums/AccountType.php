<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum AccountType: string
{
    use EnumToArray;

    case CHECKING = 'checking';
    case SAVING = 'saving';
    case LOAN = 'loan';
    case INVESTMENT = 'investment';
}
