<?php

namespace App\Services\Proxy;

use App\Services\Contracts\ProfileServiceInterface;
use App\Traits\AroundTrait;

class ProfileServiceProxy implements ProfileServiceInterface
{
    use AroundTrait ;

    public function __construct(
        protected ProfileServiceInterface $inner
    ){}

}
