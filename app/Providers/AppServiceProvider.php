<?php

namespace App\Providers;

use App\Services\AgencyService;
use App\Services\Aspects\AgencyServiceAspect;
use App\Services\Aspects\AuthServiceAspect;
use App\Services\Aspects\ComplaintServiceAspect;
use App\Services\Aspects\ProfileServiceAspect;
use App\Services\AuthService;
use App\Services\ComplaintService;
use App\Services\Contracts\AgencyServiceInterface;
use App\Services\Contracts\AuthServiceInterface;
use App\Services\Contracts\ComplaintServiceInterface;
use App\Services\Contracts\ProfileServiceInterface;
use App\Services\ProfileService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AgencyServiceInterface::class,function ($app){
            $inner = $app->make(AgencyService::class);

            return new AgencyServiceAspect($inner);
        });

        $this->app->bind(ComplaintServiceInterface::class, function($app){
            $inner = $app->make(ComplaintService::class);

            return new ComplaintServiceAspect($inner);
        });

        $this->app->bind(AuthServiceInterface::class,function($app){
            $inner = $app->make(AuthService::class);

            return new AuthServiceAspect($inner);
        });

        $this->app->bind(ProfileServiceInterface::class,function($app){
            $inner = $app->make(ProfileService::class);

            return new ProfileServiceAspect($inner);
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
