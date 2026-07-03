<?php

namespace App\Providers;

use App\Models\Order;
use App\Observers\OrderObserver;
use App\Support\Database\DatabaseModeManager;
use App\Support\Diagnostics\RequestDiagnostics;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->scoped(RequestDiagnostics::class, fn () => new RequestDiagnostics);
        $this->app->singleton(DatabaseModeManager::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        app(DatabaseModeManager::class)->apply();

        Order::observe(OrderObserver::class);

        if (config('logging.diagnostics.enabled')) {
            DB::listen(function (QueryExecuted $query): void {
                app(RequestDiagnostics::class)->recordQuery($query);
            });
        }
    }
}
