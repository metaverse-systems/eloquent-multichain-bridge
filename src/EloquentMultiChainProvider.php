<?php

namespace MetaverseSystems\EloquentMultiChainBridge;

use Illuminate\Support\ServiceProvider;
use MetaverseSystems\EloquentMultiChainBridge\Commands\RegisterDataStream;
use MetaverseSystems\EloquentMultiChainBridge\Commands\SyncStreamFromChain;
use MetaverseSystems\EloquentMultiChainBridge\Commands\SyncChainFromModels;

class EloquentMultiChainProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->commands([
            RegisterDataStream::class,
            SyncStreamFromChain::class,
            SyncChainFromModels::class
        ]);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__.'/Migrations');

        if(!$this->app->routesAreCached())
        {
            require __DIR__.'/Routes.php';
        }

        $this->publishes([
            __DIR__.'/../config/config.php' => config_path('eloquent-multichain-bridge.php'),
        ], 'config');
    }
}

