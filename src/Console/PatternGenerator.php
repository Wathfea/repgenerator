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
    protected $signature = 'pattern:generator {name : Class (singular) for example User}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Laravel Repository Pattern';

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

        $this->info('Generating...');
        $this->model($name);
        $this->controller($name);
        $this->apiController('v1', $name);
        $this->request($name);
        $this->updateRequest($name);
        $this->eloquent($name);
        $this->interface($name);
        $this->service($name);
        $this->viewModel($name);
        $this->transformer($name);
        $this->views($name);

        File::append(base_path('routes/api.php'), 'Route::resource(\'' . Str::plural(strtolower($name)) . "', {$name}ApiController::class)->only(['store', 'update', 'destroy']);\n\r");
        File::append(base_path('routes/web.php'), 'Route::resource(\'' . Str::plural(strtolower($name)) . "', {$name}Controller::class)->only(['index', 'create', 'edit']);\n\r");

        $this->info('Generate finished!');
    }

    /**
     * @param string $name
     */
    protected function model(string $name)
    {
        $modelTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Model')
        );

        file_put_contents(app_path("Models/{$name}.php"), $modelTemplate);
    }

    /**
     * @param string $name
     * @return false|string
     */
    protected function getStub(string $name)
    {
        return file_get_contents(__DIR__ . "/stubs/$name.stub");
    }

    /**
     * @param string $name
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

        file_put_contents(app_path("/Http/Controllers/Web/{$name}Controller.php"), $controllerTemplate);
    }

    /**
     * @param string $version
     * @param string $name
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

        file_put_contents(app_path("/Http/Controllers/Api/{$version}/{$name}ApiController.php"), $apiControllerTemplate);
    }

    /**
     * @param string $name
     */
    protected function request(string $name)
    {
        $requestTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Request')
        );

        if (!file_exists($path = app_path('/Http/Requests'))) {
            mkdir($path, 0777, true);
        }

        file_put_contents(app_path("/Http/Requests/{$name}Request.php"), $requestTemplate);
    }

    /**
     * @param string $name
     */
    protected function updateRequest(string $name)
    {
        $updateRequestTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('UpdateRequest')
        );

        if (!file_exists($path = app_path('/Http/Requests'))) {
            mkdir($path, 0777, true);
        }

        file_put_contents(app_path("/Http/Requests/{$name}UpdateRequest.php"), $updateRequestTemplate);
    }

    /**
     * @param string $name
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

        if (!file_exists($path = app_path("/Domain/{$name}/Repositories"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents(app_path("Domain/{$name}/Repositories/Eloquent{$name}Repository.php"), $eloquentTemplate);
    }

    /**
     * @param string $name
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

        if (!file_exists($path = app_path("/Domain/{$name}/Repositories/Interfaces"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents(app_path("Domain/{$name}/Repositories/Interfaces/{$name}RepositoryInterface.php"), $interfaceTemplate);
    }

    /**
     * @param string $name
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

        if (!file_exists($path = app_path("/Domain/{$name}/Services"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents(app_path("Domain/{$name}/Services/{$name}Service.php"), $serviceTemplate);
    }

    /**
     * @param string $name
     */
    protected function viewModel(string $name)
    {
        $viewModelTemplate = str_replace(
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
            $this->getStub('ViewModel')
        );

        if (!file_exists($path = app_path("/Domain/{$name}/ViewModel"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents(app_path("Domain/{$name}/ViewModel/{$name}.php"), $viewModelTemplate);
    }

    /**
     * @param string $name
     */
    protected function transformer(string $name)
    {
        $transformerTemplate = str_replace(
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
            $this->getStub('Transformer')
        );

        if (!file_exists($path = app_path("/Domain/{$name}/ViewModel/Transformers"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents(app_path("Domain/{$name}/ViewModel/Transformers/{$name}Transformer.php"), $transformerTemplate);
    }

    /**
     * @param string $name
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

        if (!file_exists($path = resource_path("/views/{$lower}"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents(resource_path("views/{$lower}/index.blade.php"), $viewIndexTemplate);
        file_put_contents(resource_path("views/{$lower}/create.blade.php"), $viewEmptyTemplate);
        file_put_contents(resource_path("views/{$lower}/edit.blade.php"), $viewEmptyTemplate);
    }
}
