<?php

namespace App\Enums;
use App\Traits\EnumToArray;

enum OtpCodePurpose: string
{
    use EnumToArray;

    case Verification = 'email_verification';
    case Reset = 'password_reset';
}
