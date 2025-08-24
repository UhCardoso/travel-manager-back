<?php

namespace App\Providers;

use App\Models\TravelRequest;
use App\Observers\TravelRequestObserver;
use App\Repositories\Contracts\AdminTravelRequestRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\UserTravelRequestRepositoryInterface;
use App\Repositories\Eloquent\AdminTravelRequestRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use App\Repositories\Eloquent\UserTravelRequestRepository;
use App\Services\AdminTravelRequestService;
use App\Services\AuthAdminService;
use App\Services\AuthUserService;
use App\Services\UserTravelRequestService;
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
        $this->app->bind(UserTravelRequestRepositoryInterface::class, UserTravelRequestRepository::class);
        $this->app->bind(AdminTravelRequestRepositoryInterface::class, AdminTravelRequestRepository::class);

        // Bind services of authentication
        $this->app->bind(AuthAdminService::class, function ($app) {
            return new AuthAdminService($app->make(UserRepositoryInterface::class));
        });

        $this->app->bind(AuthUserService::class, function ($app) {
            return new AuthUserService($app->make(UserRepositoryInterface::class));
        });

        // Bind admin travel request service
        $this->app->bind(AdminTravelRequestService::class, function ($app) {
            return new AdminTravelRequestService($app->make(AdminTravelRequestRepositoryInterface::class));
        });

        // Bind user travel request service
        $this->app->bind(UserTravelRequestService::class, function ($app) {
            return new UserTravelRequestService($app->make(UserTravelRequestRepositoryInterface::class), $app->make(UserRepositoryInterface::class));
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Register observers
        TravelRequest::observe(TravelRequestObserver::class);
    }
}
