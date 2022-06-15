<?php

namespace Pentacom\Repgenerator;

use Illuminate\Support\Str;

/**
 * Class RepgeneratorService
 */
class RepgeneratorService
{
    /**
     * @var int $charsCount
     */
    public $charsCount = 0;

    protected $cmd;

    /**
     * @param  mixed  $cmd
     */
    public function setCmd($cmd): void
    {
        $this->cmd = $cmd;
    }


    private function countChars($file) {
        $count = 0;

        $fh = fopen($file, 'r');
        while(!feof($fh)){
            $fr = fread($fh, 8192);
            $count += strlen($fr);
        }
        fclose($fh);

        $this->charsCount += $count;
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

        $this->copyStaticFiles($callback);
        $callback('Static files generated!');


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

        $this->filter($name, $columns, $foreigns);
        $callback('Filter is ready!');

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
        $callback($str);


        $callback("Code generation has saved you from typing at least {$this->charsCount} characters");
        $mins = floor(($this->charsCount / 5) / 25);
        $hours = floor($mins / 60);

        $callback("If we count an avarage 5 char word and an avarage 25 WPM we saved you around {$mins} minutes -> {$hours} hours");
    }

    /**
     * Create static files
     */
    private function copyStaticFiles($callback)
    {
        $files = [
            "Abstraction/Models/BaseModel.php",
            "Abstraction/Filter/BaseQueryFilter.php",
            "Abstraction/Repository/HasRepositoryService.php",
            "Abstraction/Repository/HasModelRepositoryService.php",
            "Abstraction/Repository/HasPivotRepositoryService.php",
            "Abstraction/Repository/AbstractModelRepositoryService.php",
            "Abstraction/Repository/AbstractPivotRepositoryService.php",
            "Abstraction/Repository/AbstractRepositoryService.php",
            "Abstraction/Repository/ModelRepositoryServiceInterface.php",
            "Abstraction/Repository/PivotRepositoryServiceInterface.php",
            "Abstraction/Repository/RepositoryServiceInterface.php",
            "Abstraction/Controllers/BaseTransactionController.php",
            "Abstraction/Controllers/AbstractApiReadOnlyCRUDController.php",
            "Abstraction/Controllers/AbstractApiReadWriteCRUDController.php",
            "Abstraction/Controllers/AbstractCRUDController.php",
            "Abstraction/Controllers/CRUDControllerInterface.php",
            "Abstraction/Controllers/ApiCRUDControllerReadOnlyInterface.php",
            "Abstraction/Controllers/ApiCRUDControllerReadWriteInterface.php",
        ];

        foreach ( $files as $fileOriginal ) {
            $fileParts = explode('/', $fileOriginal);
            $nameWithExtension = end($fileParts);
            $name = explode('.', $nameWithExtension)[0];
            $file = $this->getStatic($name);

            if (!file_exists($path = app_path($fileOriginal))) {
                file_put_contents($path, $file);

                $this->countChars($path);

                $this->generatedFiles[] = [
                    'name' => $nameWithExtension,
                    'location' => $path
                ];
            }
            $callback($name . ' is ready!');
        }
    }


    /**
     * @param  string  $name
     * @return false|string
     */
    private function getFilterStub(string $name)
    {
        return file_get_contents(__DIR__."/stubs/Filter/$name.stub");
    }

    /**
     * @param  string  $name
     * @return false|string
     */
    private function getStub(string $name)
    {
        return file_get_contents(__DIR__."/stubs/$name.stub");
    }

    /**
     * @param  string  $name
     * @return false|string
     */
    private function getStatic(string $name)
    {
        return file_get_contents(__DIR__."/statics/$name.php");
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
            $this->getStub('Model')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Models/"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Models/{$name}.php"), $modelTemplate);

        $this->countChars($path);

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
            $this->getStub('ModelPivot')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Models/"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Models/{$name}.php"), $modelTemplate);

        $this->countChars($path);

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
        $stub = $this->getStub('ApiControllerReadWrite');
        if($readOnly) {
            $stub = $this->getStub('ApiControllerReadOnly');
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

        $this->countChars($path);

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
            $this->getStub('Request')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Requests"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Requests/{$name}Request.php"), $requestTemplate);

        $this->countChars($path);

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
            $this->getStub('UpdateRequest')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Requests"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Requests/{$name}UpdateRequest.php"), $updateRequestTemplate);

        $this->countChars($path);

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
            $this->getStub('RepositoryService')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Repositories"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Repositories/{$name}RepositoryService.php"),
            $eloquentTemplate);

        $this->countChars($path);

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
            $this->getStub('Service')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Services"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Services/{$name}Service.php"), $serviceTemplate);

        $this->countChars($path);

        $this->generatedFiles[] = [
            'name' => "{$name}Service.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    private function provider(string $name)
    {
        $providerTemplate = str_replace(
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
            $this->getStub('Provider')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Providers"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Providers/{$name}ServiceProvider.php"), $providerTemplate);

        $this->countChars($path);

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
            $this->getStub('Resource')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Resources"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Resources/{$name}Resource.php"), $resourceTemplate);

        $this->countChars($path);

        $this->generatedFiles[] = [
            'name' => "{$name}Resource.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     * @param  array  $columns
     * @param  array  $foreigns
     */
    private function filter(string $name, array $columns, array $foreigns)
    {
        $columnFunctions = '';
        /** @var RepgeneratorColumnAdapter $column */
        foreach ( $columns as $column ) {
            foreach ($foreigns as $foreign) {
                if($column->name == $foreign['column']) {
                    $supportedForeignColumns = [
                        'id' => 'int'
                    ];
                    foreach ( $supportedForeignColumns as $supportedForeignColumnName => $supportedForeignColumnType ) {
                        $stub = $this->getFilterStub('Relationship');
                        $replacements = [
                            '{{foreign}}' => Str::singular($foreign['column']),
                            '{{foreignColumnName}}' => ucfirst($supportedForeignColumnName),
                            '{{foreignColumnType}}' => $supportedForeignColumnType
                        ];
                        $columnFunctions .= str_replace(array_keys($replacements), array_values($replacements), $stub);
                    }
                }
            }
        }

        $filterTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('Filter')
        );
        $filterTemplate = str_replace(
            ['{{functions}}'],
            [$columnFunctions],
            $filterTemplate
        );

        if (!file_exists($path = app_path("Domain/{$name}/Filters"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Filters/{$name}Filter.php"), $filterTemplate);

        $this->countChars($path);

        $this->generatedFiles[] = [
            'name' => "{$name}Filter.php",
            'location' => $path
        ];
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
            $this->getStub('Frontend/Vue/index')
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

        $this->countChars($path);
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
