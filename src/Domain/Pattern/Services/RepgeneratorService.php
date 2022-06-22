<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Services;

use Illuminate\Support\Str;
USE Pentacom\Repgenerator\Domain\Pattern\Adapters\RepgeneratorColumnAdapter;
use Pentacom\Repgenerator\Domain\Pattern\Helpers\CharacterCounterStore;

/**
 * Class RepgeneratorService
 */
class RepgeneratorService
{

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
     * @param RepgeneratorStubService $repgeneratorStubService
     * @param RepgeneratorStaticFilesService $repgeneratorStaticFilesService
     * @param RepgeneratorFilterService $repgeneratorFilterService
     */
    public function __construct(
        private RepgeneratorStubService $repgeneratorStubService,
        private RepgeneratorStaticFilesService $repgeneratorStaticFilesService,
        private RepgeneratorFilterService $repgeneratorFilterService) {

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
    private function generateFilters(string $name, array $columns, array $foreigns, $callback) {
        $this->generatedFiles[] = $this->repgeneratorFilterService->generate($name, $columns, $foreigns);
        $callback('Filter is ready!');
    }

    /**
     * @param  string  $name
     * @param  bool  $generateModel
     * @param  bool  $generatePivot
     * @param  false  $readOnly
     * @param  array  $columns
     * @param  array  $foreigns
     * @param $callback
     * @param  false  $fromConsole
     */
    public function generate(
        string $name,
        bool $generateModel,
        bool $generatePivot,
        bool $readOnly,
        array $columns,
        array $foreigns,
        $callback,
        bool $fromConsole = false
    ) {

        $this->createDirectories();
        $callback('Directories generated!');

        $this->generateStaticFiles($callback);

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

        $this->service($name, $generatePivot);
        $callback('Controller service is ready!');

        $this->provider($name);
        $callback('Provider is ready!');

        $this->resource($name);
        $callback('Resource is ready!');

        //$this->factory($name, $columns);
        //$callback('Factory is ready!');

        $this->generateFilters($name, $columns, $foreigns, $callback);

        $this->frontend($name, $columns);
        $callback('Frontend component is ready!');

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
            [$name],
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
     * @param  string  $name
     * @param  bool  $generatePivot
     */
    private function service(string $name, bool $generatePivot = false)
    {
        $serviceTemplate = str_replace(
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
     * @param  string  $name
     * @param  bool  $isPivot
     */
    private function provider(string $name, bool $isPivot = false)
    {
        $providerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{repoParams}}'
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
                $isPivot ? 'TODO::class' : $name.'::class'
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
     */
    private function resource(string $name)
    {
        $resourceTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
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



    public function factory(string $name, array $columns)
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


    public function frontend(string $name, array $columns) {

        $columnsToShowOnTable = [];
        /**
         * @var  $column
         * @var  RepgeneratorColumnAdapter $data
         */
        foreach ( $columns as $data ) {
            if ( $data->showOnTable  ) {
                $nameParts = explode('_', $data->name);
                foreach ( $nameParts as $index => $namePart ) {
                    $nameParts[$index] = ucfirst(strtolower($namePart));
                }
                $columnsToShowOnTable[implode(' ', $nameParts)] = $data->name;
            }
        }
        $indexTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNames}}',
                '{{modelColumns}}',
                '{{modelRoute}}',
            ],
            [
                $name,
                $name . 's',
                json_encode($columnsToShowOnTable),
                strtolower($name.'s')
            ],
            $this->repgeneratorStubService->getStub('Frontend/Vue/index')
        );

        if (!file_exists($path = resource_path("js"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = resource_path("js/" . $name))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = resource_path("js/" . $name . '/vue'))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = resource_path("js/{$name}/vue/index.vue"), $indexTemplate);

        CharacterCounterStore::addFileCharacterCount($path);
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
    }
}
