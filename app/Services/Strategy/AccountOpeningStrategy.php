<?php

namespace App\Services\Strategy;

interface AccountOpeningStrategy
{
    public function prepare(int $userId, string $accountType, string $name, string $description, float $initialAmount): AccountOpeningResult;
}
