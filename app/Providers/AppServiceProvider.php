<?php

namespace App\Providers;

use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Services\AuthAdminService;
use App\Services\AuthUserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind repositories
        $this->app->bind(UserRepositoryInterface::class, EloquentUserRepository::class);

        // Bind services of authentication
        $this->app->bind(AuthAdminService::class, function ($app) {
            return new AuthAdminService($app->make(UserRepositoryInterface::class));
        });

        $this->app->bind(AuthUserService::class, function ($app) {
            return new AuthUserService($app->make(UserRepositoryInterface::class));
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
