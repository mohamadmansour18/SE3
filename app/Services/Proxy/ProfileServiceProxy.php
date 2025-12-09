<?php

namespace App\Services\Proxy;

use App\Services\Contracts\ProfileServiceInterface;
use App\Traits\AspectTrait;

class ProfileServiceProxy implements ProfileServiceInterface
{
    use AspectTrait ;

    public function __construct(
        protected ProfileServiceInterface $inner
    ){}

}
