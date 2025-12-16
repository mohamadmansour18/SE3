<?php

namespace App\Services\Strategy;

class AccountOpeningResult
{
    public function __construct(
        public float  $initialBalance,
        public string $initialStatus,
    ) {}
}
