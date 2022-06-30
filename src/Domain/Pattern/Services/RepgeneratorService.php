<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Services;

use App\Domain\CrudMenu\Providers\CrudMenuServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Pentacom\Repgenerator\Traits\Stringable;
use Pentacom\Repgenerator\Helpers\Constants;
USE Pentacom\Repgenerator\Domain\Pattern\Adapters\RepgeneratorColumnAdapter;
use Pentacom\Repgenerator\Domain\Pattern\Helpers\CharacterCounterStore;

/**
 * Class RepgeneratorService
 */
class RepgeneratorService
{
    use Stringable;

    protected string $cmd;
    protected array $generatedFiles = [];

    /**
     * @param  mixed  $cmd
     */
    public function setCmd(string $cmd): void
    {
        $this->cmd = $cmd;
    }

    /**
     * @param  RepgeneratorStubService  $repgeneratorStubService
     * @param  RepgeneratorStaticFilesService  $repgeneratorStaticFilesService
     * @param  RepgeneratorFilterService  $repgeneratorFilterService
     * @param  RepgeneratorFrontendService  $repgeneratorFrontendService
     */
    public function __construct(
        private RepgeneratorStubService $repgeneratorStubService,
        private RepgeneratorStaticFilesService $repgeneratorStaticFilesService,
        private RepgeneratorFilterService $repgeneratorFilterService,
        private RepgeneratorFrontendService $repgeneratorFrontendService
    ) {

    }


    /**
     * @param $callback
     */
    private function generateStaticFiles($callback) {
        $staticFiles = $this->repgeneratorStaticFilesService->copyStaticFiles();
        foreach ( $staticFiles as $staticFile ) {
            $this->generatedFiles[] = $staticFile;
            CharacterCounterStore::addFileCharacterCount($staticFile->path);
            $callback($staticFile->name . ' is ready!');
        }
        $callback('Static files generated!');
    }

    /**
     * @param string $name
     * @param array $columns
     * @param array $foreigns
     * @param $callback
     */
    private function filters(string $name, array $columns, array $foreigns, $callback) {
        $this->generatedFiles[] = $this->repgeneratorFilterService->generate($name, $columns, $foreigns);
        $callback('Filter is ready!');
    }

    /**
     * @param  string  $name
     * @param  bool  $generateModel
     * @param  bool  $generatePivot
     * @param  false  $readOnly
     * @param  string|null  $uploadsFilesTo
     * @param  string|null  $migrationName
     * @param  RepgeneratorColumnAdapter[]  $columns
     * @param  array  $foreigns
     * @param $callback
     * @param  false  $fromConsole
     */
    public function generate(
        string $name,
        bool $generateModel,
        bool $generatePivot,
        bool $readOnly,
        string $uploadsFilesTo = null,
        string $migrationName = null,
        array $columns,
        array $foreigns,
        $callback,
        bool $fromConsole = false
    ) {
        $this->createDirectories();
        $callback('Directories generated!');

        $this->generateStaticFiles($callback);

        //Make sure name is singular
        $name = Str::singular($name);

        if ($generateModel) {
            if($generatePivot) {
                $this->modelPivot($name);
            } else {
                $this->model($name);
            }
            $callback('Model is ready!');
        }

        $this->apiController('v1', $name, $readOnly);
        $callback('API Controller is ready!');

        $this->request($name);
        $this->updateRequest($name);
        $callback('Controller requests are ready!');

        $this->repositoryService($name, $generatePivot);
        $callback('Repository layer is ready!');

        $this->service($name, $generatePivot, $uploadsFilesTo);
        $callback('Controller service is ready!');

        $this->provider($name, false, $uploadsFilesTo);
        $callback('Provider is ready!');

        $this->resource($name, $columns);
        $callback('Resource is ready!');

        //$this->factory($name, $columns);
        //$callback('Factory is ready!');

        $this->filters($name, $columns, $foreigns, $callback);

        $this->frontend($name, $columns, $callback);

        if ( $fromConsole ) {
            $this->cmd->newLine();
            $callback('Generated files:');
            $this->cmd->table(
                ['Name', 'Location'],
                $this->generatedFiles
            );
            $this->cmd->newLine();
        }

        $callback('Please add this line to config/app.php Application Service Providers section:');
        $str = "App\Domain\/".$name."\Providers\/".$name."ServiceProvider::class,";
        $str = str_replace('/', '', $str);
        $code['code'] = $str;
        $callback($code);


        $callback("Code generation has saved you from typing at least " . CharacterCounterStore::$charsCount . " characters");
        $minutes = floor((CharacterCounterStore::$charsCount / 5) / 25);
        $hours = floor($minutes / 60);

        $callback("If we count an average 5 char word and an average 25 WPM we saved you around {$minutes} minutes -> {$hours} hours");

        if($migrationName) {
            app()->register(CrudMenuServiceProvider::class);
            Artisan::call('migrate',
                [
                    '--path' => '/database/migrations/'.$migrationName,
                    '--force' => true]);
            $callback($migrationName.' migration migrated to database!');
        }
    }



    /**
     * @param  string  $name
     */
    private function model(string $name)
    {
        $modelTemplate = str_replace(
            [
                '{{modelName}}'
            ],
            [
                $name
            ],
            $this->repgeneratorStubService->getStub('Model')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Models/"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Models/{$name}.php"), $modelTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        $this->generatedFiles[] = [
            'name' => "{$name}.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    private function modelPivot(string $name)
    {
        $modelTemplate = str_replace(
            [
                '{{modelName}}'
            ],
            [$name],
            $this->repgeneratorStubService->getStub('ModelPivot')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Models/"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Models/{$name}.php"), $modelTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        $this->generatedFiles[] = [
            'name' => "{$name}.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $version
     * @param  string  $name
     * @param  bool  $readOnly
     */
    private function apiController(string $version, string $name, bool $readOnly = false)
    {
        if($readOnly) {
            $stub = $this->repgeneratorStubService->getStub('ApiControllerReadOnly');
        } else {
            $stub = $this->repgeneratorStubService->getStub('ApiControllerReadWrite');
        }
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
            $stub
        );

        if (!file_exists($path = app_path("Domain/{$name}/Controllers/Api/{$version}"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Controllers/Api/{$version}/{$name}ApiController.php"),
            $apiControllerTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        $this->generatedFiles[] = [
            'name' => "{$name}ApiController.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    private function request(string $name)
    {
        $requestTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->repgeneratorStubService->getStub('Request')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Requests"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Requests/{$name}Request.php"), $requestTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        $this->generatedFiles[] = [
            'name' => "{$name}Request.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    private function updateRequest(string $name)
    {
        $updateRequestTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->repgeneratorStubService->getStub('UpdateRequest')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Requests"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Requests/{$name}UpdateRequest.php"), $updateRequestTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        $this->generatedFiles[] = [
            'name' => "{$name}UpdateRequest.php",
            'location' => $path
        ];
    }

    /**
     * @param string $name
     * @param bool $generatePivot
     */
    private function repositoryService(string $name, bool $generatePivot)
    {
        $eloquentTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelType}}',
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
                $generatePivot ? 'Pivot' : 'Model'
            ],
            $this->repgeneratorStubService->getStub('RepositoryService')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Repositories"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Repositories/{$name}RepositoryService.php"),
            $eloquentTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        $this->generatedFiles[] = [
            'name' => "{$name}RepositoryService.php",
            'location' => $path
        ];
    }

    /**
     * @param string $name
     * @param bool $generatePivot
     * @param string|null $uploadsFilesTo
     */
    private function service(string $name, bool $generatePivot = false, string $uploadsFilesTo = null)
    {
        $use = "";
        $traits = "";
        if ( $uploadsFilesTo ) {
            $use .= "use App\Abstraction\Traits\UploadsFiles;\n";
            $traits .= "use UploadsFiles;\n";
        }
        $serviceTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelType}}',
                '{{use}}',
                '{{traits}}',
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
                $generatePivot ? 'Pivot' : 'Model',
                $use,
                $traits
            ],
            $this->repgeneratorStubService->getStub('Service')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Services"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Services/{$name}Service.php"), $serviceTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        $this->generatedFiles[] = [
            'name' => "{$name}Service.php",
            'location' => $path
        ];
    }

    /**
     * @param string $name
     * @param bool $isPivot
     * @param string|null $uploadsFilesTo
     */
    private function provider(string $name, bool $isPivot = false, string $uploadsFilesTo = null)
    {
        $serviceSetters = "";
        if ( $uploadsFilesTo ) {
            $serviceSetters .= "->setFilesLocation('$uploadsFilesTo')";
        }
        $providerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{repoParams}}',
                '{{serviceSetters}}'
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
                $isPivot ? 'TODO::class' : $name.'::class',
                $serviceSetters
            ],
            $this->repgeneratorStubService->getStub('Provider')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Providers"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Providers/{$name}ServiceProvider.php"), $providerTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        $this->generatedFiles[] = [
            'name' => "{$name}ServiceProvider.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     * @param  RepgeneratorColumnAdapter[]  $columns
     */
    private function resource(string $name, array $columns)
    {
        $routeName =  strtolower(Str::plural($name));

        $actions = ['index', 'store', 'update', 'show', 'destroy'];

        $lines[] = "'actions' => [";
        foreach ( $actions as $route ) {
            $templete = match ($route) {
                'index', 'store' => $this->repgeneratorStubService->getStub('actionRoute'),
                'update', 'show', 'destroy' => $this->repgeneratorStubService->getStub('actionRouteWithParam'),
            };

            $actionRouteTemplate = str_replace(
                [
                    '{{route}}', '{{routeName}}'
                ],
                [
                    $route, $routeName
                ],
                $templete
            );
            $lines[] = Constants::TAB.Constants::TAB.$actionRouteTemplate;
        }
        $lines[] = "],";

        $use = "";
        foreach ( $columns as $column ) {
            if ( $column->references ) {
                $referenceSingular = Str::singular($column->references['name']);
                $referenceName = ucfirst($referenceSingular);
                $use .= "use App\Domain\\" . $referenceName . "\\Resources\\" .  $referenceName . "Resource;\n";
                $resourceElementTemplate = str_replace(
                    [
                        '{{field}}',
                        '{{referenceName}}',
                        '{{referenceSingular}}',
                    ],
                    [
                        $column->name,
                        $referenceName,
                        $referenceSingular
                    ], $this->repgeneratorStubService->getStub('resourceElementRelation'));
            } else {
                $resourceElementTemplate = str_replace(['{{field}}'], [$column->name], $this->repgeneratorStubService->getStub('resourceElement'));
            }
            $lines[] = Constants::TAB.Constants::TAB.$resourceElementTemplate;
        }

        $resourceTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelResourceArray}}',
                '{{use}}'
            ],
            [
                $name,
                $this->implodeLines($lines, 2),
                $use
            ],
            $this->repgeneratorStubService->getStub('Resource')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Resources"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Resources/{$name}Resource.php"), $resourceTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        $this->generatedFiles[] = [
            'name' => "{$name}Resource.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     * @param  array  $columns
     */
    private function factory(string $name, array $columns)
    {
        $columnFactoriesString = '';

        /**
         * @var  $column
         * @var  RepgeneratorColumnAdapter $data
         */
        foreach ( $columns as $data ) {
            $columnFactoriesString .= $data->name . ' => $this->faker->';
        }

        $factoryTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelColumnFactories}}',
            ],
            [
                $name,
                $columnFactoriesString
            ],
            $this->repgeneratorStubService->getStub('Factory')
        );

    }

    /**
     * @param  string  $name
     * @param  array  $columns
     * @param $callback
     */
    private function frontend(string $name, array $columns, $callback) {
        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateIndex($name, $columns);
        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateComposable($name);
        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateCreate($name, $columns);
        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateEdit($name, $columns);

        $callback('Frontend components are ready!');
    }

    /**
     * Create static file holder directories
     */
    private function createDirectories()
    {
        if (!file_exists($path = app_path("Abstraction/Controllers"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Domain"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/Filter"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/Models"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/Repository"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/Controllers"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/Enums"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path('Abstraction/Traits'))) {
            mkdir($path, 0777, true);
        }
    }
}
