<?php

namespace App\Services\Proxy;

use App\Services\AccountService;
use App\Services\Contracts\AccountServiceInterface;

class AccountServiceProxy
{
    public function __construct(
        protected AccountServiceInterface $inner
    ){}
}
