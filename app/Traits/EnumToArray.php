<?php
namespace App\Traits;

trait EnumToArray
{
    public static function convertEnumToArray(): array
    {
        return array_column(static::cases() , 'value');
    }
}
