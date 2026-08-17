<?php

namespace App\Providers;

use App\Contracts\IdentityProvider;
use App\Services\Identity\SimulatedIdentityProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use LogicException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(IdentityProvider::class, function () {
            return match (config('identity.provider')) {
                'simulated' => app(SimulatedIdentityProvider::class),
                default => throw new LogicException('El proveedor de identidad configurado no está implementado.'),
            };
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();
    }
}
