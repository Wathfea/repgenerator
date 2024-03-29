<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Providers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Pentacom\Repgenerator\Console\ClearFiles;
use Pentacom\Repgenerator\Console\MigrationGenerator;
use Pentacom\Repgenerator\Console\PatternGenerator;
use Pentacom\Repgenerator\Console\PatternGeneratorInit;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorFilterService;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorFrontendFrameworkHandlerService;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorFrontendService;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorNameTransformerService;
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
            // Exxport the config
            $this->publishes([
                __DIR__.'/../../../../config/config.php' => config_path('repgenerator.php'),
            ], 'config');

            // Registering package commands.
            $this->commands([PatternGeneratorInit::class]);
            $this->commands([ClearFiles::class]);
        }


        $this->app->singleton(RepgeneratorStubService::class, function () {
            return (new RepgeneratorStubService(__DIR__.'/../../../resources/stubs/'));
        });

        $this->app->singleton(RepgeneratorFrontendFrameworkHandlerService::class, function () {
            return (new RepgeneratorFrontendFrameworkHandlerService());
        });

        $this->app->singleton(RepgeneratorStaticFilesService::class, function () {
            return (new RepgeneratorStaticFilesService(__DIR__.'/../../../resources/statics/Backend/',
                __DIR__.'/../../../resources/statics/Frontend/'
            ));
        });

        $this->app->singleton(RepgeneratorFilterService::class, function () {
            return (new RepgeneratorFilterService(
                app(RepgeneratorStubService::class)
            ));
        });

        $this->app->singleton(RepgeneratorNameTransformerService::class, function () {
            return (new RepgeneratorNameTransformerService());
        });

        $this->app->singleton(RepgeneratorFrontendService::class, function () {
            return (new RepgeneratorFrontendService(
                app(RepgeneratorStubService::class),
                app(RepgeneratorNameTransformerService::class),
            ));
        });

        $this->app->singleton(RepgeneratorService::class, function () {
            return (new RepgeneratorService(
                app(RepgeneratorStubService::class),
                app(RepgeneratorStaticFilesService::class),
                app(RepgeneratorFilterService::class),
                app(RepgeneratorFrontendService::class),
                app(RepgeneratorNameTransformerService::class)
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
