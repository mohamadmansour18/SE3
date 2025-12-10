<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum AccountType: string
{
    use EnumToArray;

    case CHECKING = 'جاري';
    case SAVING = 'توفير';
    case LOAN = 'قرض';
    case INVESTMENT = 'استثماري';
}
