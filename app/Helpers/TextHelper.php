<?php

namespace App\Helpers;

class TextHelper
{
    public static function fixBidi(string $text): string
    {
        $hasArabic = preg_match('/\p{Arabic}/u', $text);

        $hasEnglish = preg_match('/[A-Za-z]/', $text);

        if ($hasArabic && $hasEnglish) {
            return "\u{202B}{$text}\u{202C}";
        }

        return $text;
    }
}
