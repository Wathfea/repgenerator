<?php

namespace Pentacom\Repgenerator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Class PatternGenerator.
 */
class PatternGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pattern:generate 
                        {name : Class (singular) for example User} 
                        {--model : Whether the generator should generate a model}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Laravel Repository Pattern';

    /**
     * @var array
     */
    protected $generatedFiles = [];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate the pattern files.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $generateModel = $this->option('model');

        $this->info('Generating...');
        $this->copyStaticFiles();

        if ($generateModel) {
            $this->model($name);
            $this->info('Model is ready!');
        }

        $this->controller($name);
        $this->info('Controller is ready!');

        $this->apiController('v1', $name);
        $this->info('API Controller is ready!');

        $this->request($name);
        $this->updateRequest($name);
        $this->info('Controller requests are ready!');

        $this->eloquent($name);
        $this->interface($name);
        $this->info('Repository layer is ready!');

        $this->service($name);
        $this->info('Controller service is ready!');

        $this->provider($name);
        $this->info('Provider is ready!');

        $this->resource($name);
        $this->info('Resource is ready!');

        $this->filter($name);
        $this->info('Filter is ready!');

        $this->views($name);
        $this->info('Base views are ready!');

        $this->routes($name);
        $this->info('Routes are ready!');

        $this->newLine();
        $this->info('Generated files:');
        $this->table(
            ['Name', 'Location'],
            $this->generatedFiles
        );

        $this->newLine();
        $this->info('Please add this line to config/app.php Application Service Providers section:');
        $this->info("App\Domain\/".$name."\Providers\/".$name."ServiceProvider::class,");

        $this->newLine();
        $this->info('Please add this line to config/role-middlewares.php controllers section:');
        $lowerName = strtolower($name);
        $this->info("'{$lowerName}' => [],");

    }

    /**
     * Create static directories and copy static files
     */
    protected function copyStaticFiles()
    {
        //Create directories
        if (!file_exists($path = app_path("Http/Controllers"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Domain"))) {
            mkdir($path, 0777, true);
        }

        //BaseTransactionController
        $baseController = $this->getStub('BaseTransactionController');
        if (!file_exists($path = app_path("Http/Controllers/BaseTransactionController.php"))) {
            file_put_contents($path, $baseController);
            $this->generatedFiles[] = [
                'name' => 'BaseTransactionController.php',
                'location' => $path
            ];
        }
        $this->info('Base Controller is ready!');

        //BaseModel
        $baseModel = $this->getStub('BaseModel');
        if (!file_exists($path = app_path("Models/BaseModel.php"))) {
            file_put_contents($path, $baseModel);
            $this->generatedFiles[] = [
                'name' => 'BaseModel.php',
                'location' => $path
            ];
        }
        $this->info('Base Model is ready!');

        //AbstractEloquentModelRepository
        $abstractEloquentModelRepository = $this->getStub('AbstractEloquentModelRepository');
        if (!file_exists($path = app_path("Domain/AbstractEloquentModelRepository.php"))) {
            file_put_contents($path, $abstractEloquentModelRepository);
            $this->generatedFiles[] = [
                'name' => 'AbstractEloquentModelRepository.php',
                'location' => $path
            ];
        }
        $this->info('AbstractEloquentModelRepository is ready!');

        //AbstractEloquentPivotRepository
        $abstractEloquentPivotRepository = $this->getStub('AbstractEloquentPivotRepository');
        if (!file_exists($path = app_path("Domain/AbstractEloquentPivotRepository.php"))) {
            file_put_contents($path, $abstractEloquentPivotRepository);
            $this->generatedFiles[] = [
                'name' => 'AbstractEloquentPivotRepository.php',
                'location' => $path
            ];
        }
        $this->info('AbstractEloquentPivotRepository is ready!');

        //BaseQueryFilter
        $baseQueryFilter = $this->getStub('BaseQueryFilter');
        if (!file_exists($path = app_path("Domain/BaseQueryFilter.php"))) {
            file_put_contents($path, $baseQueryFilter);
            $this->generatedFiles[] = [
                'name' => 'BaseQueryFilter.php',
                'location' => $path
            ];
        }
        $this->info('BaseQueryFilter is ready!');

        //EloquentModelRepositoryInterface
        $eloquentModelRepositoryInterface = $this->getStub('EloquentModelRepositoryInterface');
        if (!file_exists($path = app_path("Domain/EloquentModelRepositoryInterface.php"))) {
            file_put_contents($path, $eloquentModelRepositoryInterface);
            $this->generatedFiles[] = [
                'name' => 'EloquentModelRepositoryInterface.php',
                'location' => $path
            ];
        }
        $this->info('EloquentModelRepositoryInterface is ready!');

        //EloquentPivotRepositoryInterface
        $eloquentPivotRepositoryInterface = $this->getStub('EloquentPivotRepositoryInterface');
        if (!file_exists($path = app_path("Domain/EloquentPivotRepositoryInterface.php"))) {
            file_put_contents($path, $eloquentPivotRepositoryInterface);
            $this->generatedFiles[] = [
                'name' => 'EloquentPivotRepositoryInterface.php',
                'location' => $path
            ];
        }
        $this->info('EloquentPivotRepositoryInterface is ready!');

        //ModelRepositoryInterface
        $modelRepositoryInterface = $this->getStub('ModelRepositoryInterface');
        if (!file_exists($path = app_path("Domain/ModelRepositoryInterface.php"))) {
            file_put_contents($path, $modelRepositoryInterface);
            $this->generatedFiles[] = [
                'name' => 'ModelRepositoryInterface.php',
                'location' => $path
            ];
        }
        $this->info('ModelRepositoryInterface is ready!');

        //PivotRepositoryInterface
        $pivotRepositoryInterface = $this->getStub('PivotRepositoryInterface');
        if (!file_exists($path = app_path("Domain/PivotRepositoryInterface.php"))) {
            file_put_contents($path, $pivotRepositoryInterface);
            $this->generatedFiles[] = [
                'name' => 'PivotRepositoryInterface.php',
                'location' => $path
            ];
        }
        $this->info('PivotRepositoryInterface is ready!');
    }

    /**
     * @param  string  $name
     * @return false|string
     */
    protected function getStub(string $name)
    {
        return file_get_contents(__DIR__."/stubs/$name.stub");
    }

    /**
     * @param  string  $name
     */
    protected function model(string $name)
    {
        $modelTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Model')
        );

        file_put_contents($path = app_path("Models/{$name}.php"), $modelTemplate);
        $this->generatedFiles[] = [
            'name' => "{$name}.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    protected function controller(string $name)
    {
        $controllerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
            ],
            $this->getStub('Controller')
        );

        if (!file_exists($path = app_path("Http/Controllers/Web"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Http/Controllers/Web/{$name}Controller.php"), $controllerTemplate);
        $this->generatedFiles[] = [
            'name' => "{$name}Controller.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $version
     * @param  string  $name
     */
    protected function apiController(string $version, string $name)
    {
        $apiControllerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
            ],
            $this->getStub('ApiController')
        );

        if (!file_exists($path = app_path("/Http/Controllers/Api/{$version}"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Http/Controllers/Api/{$version}/{$name}ApiController.php"),
            $apiControllerTemplate);
        $this->generatedFiles[] = [
            'name' => "{$name}ApiController.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    protected function request(string $name)
    {
        $requestTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Request')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Requests"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Requests/{$name}Request.php"), $requestTemplate);
        $this->generatedFiles[] = [
            'name' => "{$name}Request.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    protected function updateRequest(string $name)
    {
        $updateRequestTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('UpdateRequest')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Requests"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Requests/{$name}UpdateRequest.php"), $updateRequestTemplate);
        $this->generatedFiles[] = [
            'name' => "{$name}UpdateRequest.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    protected function eloquent(string $name)
    {
        $eloquentTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
            ],
            $this->getStub('Eloquent')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Repositories"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Repositories/Eloquent{$name}ModelRepository.php"),
            $eloquentTemplate);
        $this->generatedFiles[] = [
            'name' => "Eloquent{$name}ModelRepository.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    protected function interface(string $name)
    {
        $interfaceTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
            ],
            $this->getStub('Interface')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Repositories/Interfaces"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Repositories/Interfaces/{$name}ModelRepositoryInterface.php"),
            $interfaceTemplate);
        $this->generatedFiles[] = [
            'name' => "{$name}ModelRepositoryInterface.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    protected function service(string $name)
    {
        $serviceTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
            ],
            $this->getStub('Service')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Services"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Services/{$name}Service.php"), $serviceTemplate);
        $this->generatedFiles[] = [
            'name' => "{$name}Service.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    protected function provider(string $name)
    {
        $providerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNameSingularLowerCase}}',
            ],
            [
                $name,
                strtolower($name),
            ],
            $this->getStub('Provider')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Providers"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Providers/{$name}ServiceProvider.php"), $providerTemplate);
        $this->generatedFiles[] = [
            'name' => "{$name}ServiceProvider.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    protected function resource(string $name)
    {
        $resourceTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Resource')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Resources"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Resources/{$name}Resource.php"), $resourceTemplate);
        $this->generatedFiles[] = [
            'name' => "{$name}Resource.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    protected function filter(string $name)
    {
        $filterTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Filter')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Filters"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Filters/{$name}Filter.php"), $filterTemplate);
        $this->generatedFiles[] = [
            'name' => "{$name}Filter.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    protected function views(string $name)
    {
        $viewIndexTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
            ],
            $this->getStub('viewIndex')
        );

        $viewEmptyTemplate = $this->getStub('viewEmpty');

        $lower = strtolower(Str::plural($name));

        if (!file_exists($path = resource_path("views/{$lower}"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = resource_path("views/{$lower}/index.blade.php"), $viewIndexTemplate);
        $this->generatedFiles[] = [
            'name' => "views/{$lower}/index.blade.php",
            'location' => $path
        ];

        file_put_contents($path = resource_path("views/{$lower}/create.blade.php"), $viewEmptyTemplate);
        $this->generatedFiles[] = [
            'name' => "views/{$lower}/create.blade.php",
            'location' => $path
        ];

        file_put_contents($path = resource_path("views/{$lower}/edit.blade.php"), $viewEmptyTemplate);
        $this->generatedFiles[] = [
            'name' => "views/{$lower}/edit.blade.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    protected function routes(string $name)
    {
        File::append(base_path('routes/web.php'), '
        use App\Http\Controllers\Web\\'.$name.'Controller;
        Route::resource(\''.Str::plural(strtolower($name))."', {$name}Controller::class)->only(['index', 'create', 'edit']);\n\r");

        $this->generatedFiles[] = [
            'name' => "web.php",
            'location' => base_path('routes/web.php')
        ];

        File::append(base_path('routes/api.php'), '
        use App\Http\Controllers\Api\v1\\'.$name.'ApiController;
        Route::resource(\''.Str::plural(strtolower($name))."', {$name}ApiController::class, [
            'names' => [
                'index' => 'api.".Str::plural(strtolower($name)).".index',
                'store' => 'api.".Str::plural(strtolower($name)).".store',
                'update' => 'api.".Str::plural(strtolower($name)).".update',
                'destroy' => 'api.".Str::plural(strtolower($name)).".destroy',
            ],
        ])->only(['index', 'store', 'update', 'destroy']);\n\r" );

        $this->generatedFiles[] = [
            'name' => "api.php",
            'location' => base_path('routes/api.php')
        ];
    }
}
