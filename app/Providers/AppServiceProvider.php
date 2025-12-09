<?php

namespace App\Providers;

use App\Services\AccountService;
use App\Services\Contracts\AccountServiceInterface;
use App\Services\Contracts\TransactionServiceInterface;
use App\Services\Proxy\AccountServiceProxy;
use App\Services\Proxy\AuthServiceProxy;
use App\Services\Proxy\ProfileServiceProxy;
use App\Services\AuthService;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\ProfileServiceInterface;
use App\Services\ProfileService;
use App\Services\Proxy\TransactionServiceProxy;
use App\Services\TransactionService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthServiceInterface::class,function($app){
            $inner = $app->make(AuthService::class);

            return new AuthServiceProxy($inner);
        });

        $this->app->bind(ProfileServiceInterface::class,function($app){
            $inner = $app->make(ProfileService::class);

            return new ProfileServiceProxy($inner);
        });

        $this->app->bind(TransactionServiceInterface::class,function($app){
            $inner = $app->make(TransactionService::class);

            return new TransactionServiceProxy($inner);
        });

        $this->app->bind(AccountServiceInterface::class,function($app){
            $inner = $app->make(AccountService::class);

            return new AccountServiceProxy($inner);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
