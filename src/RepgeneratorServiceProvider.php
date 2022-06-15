<?php

namespace Pentacom\Repgenerator;

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
            $this->app['router']->get('repgenerator/tables', [RepgeneratorController::class, 'getTables'])->name('repgenerator.tables');
            $this->app['router']->post('repgenerator/generate', [RepgeneratorController::class, 'generate'])->name('repgenerator.generate');
            $this->app['router']->get('repgenerator/migration', [RepgeneratorController::class, 'migrationTesting'])->name('repwizz.migrationTest');

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
