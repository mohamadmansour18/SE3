<?php

namespace App\Services\Proxy;

use App\Models\User;
use App\Services\Contracts\ProfileServiceInterface;
use App\Traits\AspectTrait;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class ProfileServiceAspect implements ProfileServiceInterface
{
    use AspectTrait ;

    public function __construct(
        protected ProfileServiceInterface $inner
    ){}

}
