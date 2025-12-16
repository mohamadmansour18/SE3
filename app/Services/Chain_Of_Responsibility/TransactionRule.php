<?php

namespace App\Services\Chain_Of_Responsibility;

interface TransactionRule
{
    public function setNext(?TransactionRule $next): TransactionRule;

    public function check(TransactionContext $context): void;
}
