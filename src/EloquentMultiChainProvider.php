<?php

namespace MetaverseSystems\EloquentMultiChainBridge;

use Illuminate\Support\ServiceProvider;
use MetaverseSystems\EloquentMultiChainBridge\Commands\RegisterDataStream;

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
            RegisterDataStream::class
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
    }
}

