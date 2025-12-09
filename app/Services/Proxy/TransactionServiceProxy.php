<?php

namespace App\Services\Proxy;

use App\Services\Contracts\AccountServiceInterface;

class TransactionServiceProxy
{
    public function __construct(
        protected AccountServiceInterface $inner
    ){}
}
