<?php

namespace App\Providers;

use App\Services\MoneroRPCService;
use Illuminate\Support\ServiceProvider;

class MoneroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Register MoneroRPCService as a singleton
        $this->app->singleton('monero.rpc', function ($app) {
            return new MoneroRPCService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__.'/../../config/monero.php' => config_path('monero.php'),
        ], 'monero-config');

        // Merge configuration
        $this->mergeConfigFrom(
            __DIR__.'/../../config/monero.php', 'monero'
        );
    }
}
