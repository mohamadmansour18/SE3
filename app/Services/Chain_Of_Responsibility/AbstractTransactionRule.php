<?php

namespace App\Services\Chain_Of_Responsibility;

abstract class AbstractTransactionRule implements TransactionRule
{
    private ?TransactionRule $next = null ;

    public function setNext(?TransactionRule $next): TransactionRule
    {
        $this->next = $next;
        return $next ?? $this;
    }

    public function check(TransactionContext $context): void
    {
        $this->doCheck($context);

        if ($this->next !== null) {
            $this->next->check($context);
        }
    }

    abstract protected function doCheck(TransactionContext $context): void;
}
