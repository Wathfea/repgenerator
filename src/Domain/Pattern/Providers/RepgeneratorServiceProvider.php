<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Providers;

use Pentacom\Repgenerator\Console\MigrationGenerator;
use Pentacom\Repgenerator\Console\PatternGenerator;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorFilterService;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorService;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorStaticFilesService;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorStubService;
use Pentacom\Repgenerator\Http\Controllers\RepgeneratorController;
use App\Providers\RouteServiceProvider;

/**
 * Class RepgeneratorServiceProvider
 */
class RepgeneratorServiceProvider extends RouteServiceProvider
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


        $this->app->singleton(RepgeneratorStubService::class, function() {
            return (new RepgeneratorStubService(__DIR__. '/../../../resources/stubs/'));
        });

        $this->app->singleton(RepgeneratorStaticFilesService::class, function() {
            return (new RepgeneratorStaticFilesService(__DIR__. '/../../../resources/statics/'));
        });

        $this->app->singleton(RepgeneratorFilterService::class, function() {
            return (new RepgeneratorFilterService(
                app(RepgeneratorStubService::class)
            ));
        });

        $this->app->singleton(RepgeneratorService::class, function() {
            return (new RepgeneratorService(
                app(RepgeneratorStubService::class),
                app(RepgeneratorStaticFilesService::class),
                app(RepgeneratorFilterService::class)
            ));
        });

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
        $this->mergeConfigFrom(__DIR__ . '/../../../../config/config.php', 'repgenerator');
    }
}
