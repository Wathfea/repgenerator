<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Providers;

use Illuminate\Support\ServiceProvider;
use Pentacom\Repgenerator\Console\MigrationGenerator;
use Pentacom\Repgenerator\Console\PatternGenerator;
use Pentacom\Repgenerator\Console\PatternGeneratorInit;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorFilterService;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorFrontendService;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorService;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorStaticFilesService;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorStubService;

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
                __DIR__.'/../config/config.php' => config_path('repgenerator.php'),
            ], 'config');

            // Registering package commands.
            $this->commands([PatternGenerator::class]);
            $this->commands([PatternGeneratorInit::class]);
            $this->commands([MigrationGenerator::class]);
        }


        $this->app->singleton(RepgeneratorStubService::class, function () {
            return (new RepgeneratorStubService(__DIR__.'/../../../resources/stubs/'));
        });

        $this->app->singleton(RepgeneratorStaticFilesService::class, function () {
            return (new RepgeneratorStaticFilesService(__DIR__.'/../../../resources/statics/'));
        });

        $this->app->singleton(RepgeneratorFilterService::class, function () {
            return (new RepgeneratorFilterService(
                app(RepgeneratorStubService::class)
            ));
        });

        $this->app->singleton(RepgeneratorFrontendService::class, function () {
            return (new RepgeneratorFrontendService(
                app(RepgeneratorStubService::class)
            ));
        });

        $this->app->singleton(RepgeneratorService::class, function () {
            return (new RepgeneratorService(
                app(RepgeneratorStubService::class),
                app(RepgeneratorStaticFilesService::class),
                app(RepgeneratorFilterService::class),
                app(RepgeneratorFrontendService::class)
            ));
        });

        if (config('app.env') === 'local') {
            $this->loadRoutesFrom(__DIR__.'/../../../routes/web.php');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../../../../config/config.php', 'repgenerator');
    }
}
