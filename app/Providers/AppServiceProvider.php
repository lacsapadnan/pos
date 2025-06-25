<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Config;
use App\Repositories\CashflowRepository;
use App\Services\AuthorizationService;
use App\Services\ReportService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register Repository bindings
        $this->app->bind(CashflowRepository::class, CashflowRepository::class);

        // Register Service bindings
        $this->app->bind(AuthorizationService::class, AuthorizationService::class);
        $this->app->bind(ReportService::class, ReportService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        // Config::set('cache.default', 'file');
    }
}
