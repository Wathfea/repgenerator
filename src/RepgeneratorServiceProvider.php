<?php

namespace Pentacom\Repgenerator;

use Illuminate\Support\ServiceProvider;
use Pentacom\Repgenerator\Console\PatternGenerator;

class RepgeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('repgenerator.php'),
            ], 'config');

            // Registering package commands.
            $this->commands([PatternGenerator::class]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'repgenerator');
    }
}
