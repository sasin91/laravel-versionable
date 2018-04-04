<?php

namespace Sasin91\LaravelVersionable;

use Illuminate\Support\ServiceProvider;

class VersionableServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/versionable.php' => config_path('versionable.php'),
            ], 'config');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/versionable.php', 'versionable');
    }
}