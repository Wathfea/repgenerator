<?php

namespace Pentacom\Repgenerator;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;
use Pentacom\Repgenerator\Console\MigrationGenerator;
use Illuminate\Support\ServiceProvider;
use Pentacom\Repgenerator\Console\PatternGenerator;

/**
 * Class RepgeneratorServiceProvider
 */
class RepgeneratorServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('repgenerator.php'),
            ], 'config');

            // Registering package commands.
            $this->commands([PatternGenerator::class]);
            $this->commands([MigrationGenerator::class]);
        }

        if ( config('app.env') === 'local' ) {
            $this->loadViewsFrom(__DIR__.'/resources/views', 'repgenerator');
            $this->loadViewsFrom(__DIR__.'/resources/views/wizzard', 'repgenerator-wizzard');

            $this->app['router']->middleware('web')->get('repwizz', [RepgeneratorController::class, 'wizzard']);
            $this->app['router']->middleware('web')->get('repwizz/step/{step}', [RepgeneratorController::class, 'wizzardStep'])->name('repwizz.step');
            $this->app['router']->middleware('web')->post('repwizz/finish', [RepgeneratorController::class, 'wizzardInstall'])->name('repwizz.finish');

            $this->app['router']->get('repwizz/migration', [RepgeneratorController::class, 'migrationTesting'])->name('repwizz.migrationTest');

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
