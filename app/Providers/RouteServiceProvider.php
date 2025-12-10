<?php

namespace App\Providers;

use App\Enums\UserRole;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/home';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }

    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by(
                optional($request->user())->id ?: $request->ip()
            );
        });

        RateLimiter::for('globalApi' , function(Request $request) {
            $key = optional($request->user())->id ? 'user:' . $request->user()->id : 'ip:' . $request->ip();
            $max = config('rateLimits.globalApi' , 60);
            return Limit::perMinute($max)->by($key);
        });

        RateLimiter::for('loginApi' , function (Request $request) {
            $key = 'login:' . ($request->ip());
            $max = config('rateLimits.loginApi' , 5);
            return Limit::perMinute($max)->by($key);
        });

        RateLimiter::for('registerApi' , function (Request $request) {
            $key = 'register:' . ($request->ip());
            $max = config('rateLimits.registerApi' , 3);
            return Limit::perMinute($max)->by($key);
        });

        RateLimiter::for('forgotPasswordApi' , function (Request $request) {
            $key = 'forgotPassword:' . ($request->ip());
            $max = config('rateLimits.forgotPasswordApi' , 4);
            return Limit::perMinute($max)->by($key);
        });

        RateLimiter::for('roleBasedApi' , function (Request $request) {
            $user = $request->user();
            $baseKey = $user ? 'user:' . $user->id : 'ip:' . $request->ip();

            if($user && $user->role === UserRole::MANAGER->value)
            {
                $max = config('rateLimits.manager' , 100);
                return Limit::perMinute($max)->by($baseKey);
            }
            if($user && $user->role === UserRole::TELLER->value)
            {
                $max = config('rateLimits.teller' , 75);
                return Limit::perMinute($max)->by($baseKey);
            }
            if($user && $user->role === UserRole::CITIZEN->value)
            {
                $max = config('rateLimits.citizen' , 25);
                return Limit::perMinute($max)->by($baseKey);
            }
            return Limit::perMinute(10)->by($baseKey);
        });
    }
}
