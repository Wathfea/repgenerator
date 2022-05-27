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
            $count += substr_count($fr, 'a');
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
     * @param $callback
     * @param  false  $fromConsole
     */
    public function generate(string $name, bool $generateModel, bool $generatePivot, bool $readOnly, array $columns, $callback, bool $fromConsole = false
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

        $this->controller($name, $readOnly);
        $callback('Controller is ready!');

        $this->apiController('v1', $name, $readOnly);
        $callback('API Controller is ready!');

        $this->request($name);
        $this->updateRequest($name);
        $callback('Controller requests are ready!');

        $this->eloquent($name);
        $this->interface($name);
        $callback('Repository layer is ready!');

        $this->service($name, $generatePivot);
        $this->serviceInterface($name);
        $callback('Controller service is ready!');

        $this->provider($name);
        $callback('Provider is ready!');

        $this->resource($name);
        $callback('Resource is ready!');

        $this->filter($name, $columns);
        $callback('Filter is ready!');

        $this->views($name);
        $callback('Base views are ready!');

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
    }

    /**
     * Create static files
     */
    private function copyStaticFiles($callback)
    {
        $files = [
            "Abstraction/Models/BaseModel.php",
            "Abstraction/Repository/AbstractRepository.php",
            "Abstraction/Filter/BaseQueryFilter.php",
            "Abstraction/Repository/Eloquent/Model/AbstractEloquentModelRepository.php",
            "Abstraction/Repository/Eloquent/Model/EloquentModelRepositoryInterface.php",
            "Abstraction/Repository/Eloquent/Pivot/AbstractEloquentPivotRepository.php",
            "Abstraction/Repository/Eloquent/Pivot/EloquentPivotRepositoryInterface.php",
            "Abstraction/Repository/Eloquent/AbstractEloquentRepository.php",
            "Abstraction/Repository/Eloquent/EloquentRepositoryInterface.php",
            "Abstraction/Repository/Service/Model/AbstractModelRepositoryService.php",
            "Abstraction/Repository/Service/Model/ModelRepositoryServiceInterface.php",
            "Abstraction/Repository/Service/Pivot/AbstractPivotRepositoryService.php",
            "Abstraction/Repository/Service/Pivot/PivotRepositoryServiceInterface.php",
            "Abstraction/Repository/Service/AbstractRepositoryService.php",
            "Abstraction/Repository/Service/RepositoryServiceInterface.php",
            "Abstraction/Repository/ModelRepositoryInterface.php",
            "Abstraction/Repository/PivotRepositoryInterface.php",
            "Abstraction/Repository/RepositoryInterface.php",
            "Abstraction/Controllers/BaseTransactionController.php",
            "Abstraction/CRUD/Controllers/AbstractApiReadOnlyCRUDController.php",
            "Abstraction/CRUD/Controllers/AbstractApiReadWriteCRUDController.php",
            "Abstraction/CRUD/Controllers/AbstractCRUDController.php",
            "Abstraction/CRUD/Controllers/CRUDControllerInterface.php",
            "Abstraction/CRUD/Controllers/AbstractBladeReadOnlyCRUDController.php",
            "Abstraction/CRUD/Controllers/AbstractBladeReadWriteCRUDController.php",
            "Abstraction/CRUD/Controllers/AbstractFrontendReadOnlyCRUDController.php",
            "Abstraction/CRUD/Controllers/AbstractFrontendReadWriteCRUDController.php",
            "Abstraction/CRUD/Controllers/ApiCRUDControllerReadOnlyInterface.php",
            "Abstraction/CRUD/Controllers/ApiCRUDControllerReadWriteInterface.php",
            "Abstraction/CRUD/Controllers/BladeCRUDControllerReadOnlyInterface.php",
            "Abstraction/CRUD/Controllers/BladeCRUDControllerReadWriteInterface.php",
            "Abstraction/CRUD/Controllers/FrontendCRUDControllerReadOnlyInterface.php",
            "Abstraction/CRUD/Controllers/FrontendCRUDControllerReadWriteInterface.php",
            "Abstraction/CRUD/Enums/CRUDConfigType.php",
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
     * @param  string  $name
     * @param  bool  $readOnly
     */
    private function controller(string $name, bool $readOnly = false)
    {
        $stub = $this->getStub('ControllerReadWrite');
        if($readOnly) {
            $stub = $this->getStub('ControllerReadOnly');
        }

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
            $stub
        );

        if (!file_exists($path = app_path("Domain/{$name}/Controllers/"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Controllers/{$name}Controller.php"), $controllerTemplate);

        $this->countChars($path);

        $this->generatedFiles[] = [
            'name' => "{$name}Controller.php",
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
     * @param  string  $name
     */
    private function eloquent(string $name)
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

        $this->countChars($path);

        $this->generatedFiles[] = [
            'name' => "Eloquent{$name}ModelRepository.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     */
    private function interface(string $name)
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

        $this->countChars($path);

        $this->generatedFiles[] = [
            'name' => "{$name}ModelRepositoryInterface.php",
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
    private function serviceInterface(string $name)
    {
        $interfaceInterfaceTemplate = str_replace(
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
            $this->getStub('ServiceInterface')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Services"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Services/{$name}ServiceInterface.php"),
            $interfaceInterfaceTemplate);

        $this->countChars($path);

        $this->generatedFiles[] = [
            'name' => "{$name}ServiceInterface.php",
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
     */
    private function filter(string $name, array $columns)
    {
        $columnFunctions = '';
        /** @var RepgeneratorColumnAdapter $column */
        foreach ( $columns as $column ) {
            if ( $column->foreign ) {
                $supportedForeignColumns = [
                    'id' => 'int'
                ];
                foreach ( $supportedForeignColumns as $supportedForeignColumnName => $supportedForeignColumnType ) {
                    $stub = $this->getFilterStub('Relationship');
                    $replacements = [
                        '{{foreign}}' => Str::singular($column->foreign),
                        '{{foreignColumnName}}' => ucfirst($supportedForeignColumnName),
                        '{{foreignColumnType}}' => $supportedForeignColumnType
                    ];
                    $columnFunctions .= str_replace(array_keys($replacements), array_values($replacements), $stub);
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

    /**
     * @param  string  $name
     */
    private function views(string $name)
    {
        $viewTemplate = str_replace(
            ['{{modelName}}'],
            [$name],
            $this->getStub('viewIndex')
        );

        $lower = strtolower(Str::plural($name));

        if (!file_exists($path = resource_path("views/{$lower}"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = resource_path("views/{$lower}/index.blade.php"), $viewTemplate);
        $this->generatedFiles[] = [
            'name' => "views/{$lower}/index.blade.php",
            'location' => $path
        ];

        file_put_contents($path = resource_path("views/{$lower}/create.blade.php"), $viewTemplate);

        $this->countChars($path);

        $this->generatedFiles[] = [
            'name' => "views/{$lower}/create.blade.php",
            'location' => $path
        ];

        file_put_contents($path = resource_path("views/{$lower}/edit.blade.php"), $viewTemplate);

        $this->countChars($path);

        $this->generatedFiles[] = [
            'name' => "views/{$lower}/edit.blade.php",
            'location' => $path
        ];
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

        if (!file_exists($path = app_path("Abstraction/Repository/Eloquent"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/Repository/Eloquent/Model"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/Repository/Eloquent/Pivot"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/Repository/Service"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/Repository/Service/Model"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/Repository/Service/Pivot"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/CRUD"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/CRUD/Controllers"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/CRUD/Enums"))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = app_path("Abstraction/CRUD/Services"))) {
            mkdir($path, 0777, true);
        }
    }
}
