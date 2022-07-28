<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Services;

use App\Domain\CrudMenu\Providers\CrudMenuServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use Pentacom\Repgenerator\Domain\Pattern\Adapters\RepgeneratorColumnAdapter;
use Pentacom\Repgenerator\Domain\Pattern\Helpers\CharacterCounterStore;
use Pentacom\Repgenerator\Helpers\Constants;
use Pentacom\Repgenerator\Traits\Stringable;

/**
 * Class RepgeneratorService
 */
class RepgeneratorService
{
    use Stringable;

    /**
     * @var string
     */
    protected string $cmd;

    /**
     * @var array
     */
    protected array $generatedFiles = [];

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
     * @param  string  $name
     * @param  bool  $generateModel
     * @param  bool  $generatePivot
     * @param  bool  $generateFrontend
     * @param  false  $readOnly
     * @param  RepgeneratorColumnAdapter[]  $columns
     * @param  array  $foreigns
     * @param $callback
     * @param  false  $fromConsole
     * @param  array|null  $uploadsFiles
     * @param  string|null  $migrationName
     * @param  bool  $isGeneratedFileDomain
     */
    public function generate(
        string $name,
        bool $generateModel,
        bool $generatePivot,
        bool $generateFrontend,
        bool $readOnly,
        array $columns,
        array $foreigns,
        $callback,
        bool $fromConsole = false,
        array $uploadsFiles = null,
        string $migrationName = null,
        bool $isGeneratedFileDomain = false,
    ) {
        $this->createDirectories();
        $callback('Directories generated!');

        $this->generateStaticFiles($callback);

        //Make sure name is singular
        $name = Str::singular($name);

        if ($generateModel) {
            if ($generatePivot) {
                $this->modelPivot($name);
            } else {
                $this->model($name, $columns, $foreigns);
            }
            $callback('Model is ready!');
        }

        $this->apiController('v1', $name, $readOnly, $uploadsFiles, $isGeneratedFileDomain);
        $callback('API Controller is ready!');

        $this->request($name, $columns);
        $this->updateRequest($name, $columns);
        $callback('Controller requests are ready!');

        $this->repositoryService($name, $generatePivot, $uploadsFiles, $isGeneratedFileDomain);
        $callback('Repository layer is ready!');

        $this->service($name, $generatePivot);
        $callback('Controller service is ready!');

        $this->provider($name, false, $uploadsFiles, $isGeneratedFileDomain);
        $callback('Provider is ready!');

        $this->resource($name, $columns, $foreigns, $isGeneratedFileDomain);
        $callback('Resource is ready!');

        //$this->factory($name, $columns);
        //$callback('Factory is ready!');

        $this->filters($name, $columns, $foreigns, $callback);

        !$generateFrontend ?: $this->frontend($name, $columns, $callback);

        if ($fromConsole) {
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


        $callback("Code generation has saved you from typing at least ".CharacterCounterStore::$charsCount." characters");
        $minutes = floor((CharacterCounterStore::$charsCount / 5) / 25);
        $hours = floor($minutes / 60);

        $callback("If we count an average 5 char word and an average 25 WPM we saved you around {$minutes} minutes -> {$hours} hours");

        if ($migrationName) {
            app()->register(CrudMenuServiceProvider::class);
            Artisan::call('migrate',
                [
                    '--path' => '/database/migrations/'.$migrationName,
                    '--force' => true
                ]);
            $callback($migrationName.' migration migrated to database!');
        }
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

    /**
     * @param $callback
     */
    private function generateStaticFiles($callback)
    {
        $staticFiles = $this->repgeneratorStaticFilesService->copyStaticFiles();
        foreach ($staticFiles as $staticFile) {
            $this->generatedFiles[] = $staticFile;
            CharacterCounterStore::addFileCharacterCount($staticFile->path);
            $callback($staticFile->name.' is ready!');
        }
        $callback('Static files generated!');
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
     * @param  string  $name
     * @param  array  $columns
     * @param  array  $foreigns
     */
    private function model(string $name, array $columns, array $foreigns)
    {
        $use = [];
        $relationTemplate = [];

        if (!empty($foreigns)) {
            foreach ($foreigns as $foreign) {
                $relationType = array_key_exists('relation_type', $foreign) ? $foreign['relation_type'] : 'BelongsTo';
                $relatedModel = array_key_exists('related_model',
                    $foreign) ? $foreign['related_model'] : Str::studly(Str::singular($foreign['reference']['name']));
                $relationName = array_key_exists('relation_name',
                    $foreign) ? $foreign['relation_name'] : Str::lcfirst(Str::studly(Str::singular($foreign['reference']['name'])));

                $modelUse = 'use App\Domain\/'.$relatedModel.'\Models\/'.$relatedModel.';';
                $modelUse = str_replace('/', '', $modelUse);

                $relationUse = 'use Illuminate\Database\Eloquent\Relations\/'.$relationType.';';
                $relationUse = str_replace('/', '', $relationUse);

                if (!in_array($modelUse, $use)) {
                    $use[] = $modelUse;
                }
                if (!in_array($relationUse, $use)) {
                    $use[] = $relationUse;
                }

                $relationTemplate[] = str_replace(
                    [
                        '{{relationType}}',
                        '{{relationName}}',
                        '{{relationMethodCall}}',
                        '{{relatedModel}}'
                    ],
                    [
                        $relationType,
                        $relationName,
                        Str::camel($relationType),
                        $relatedModel
                    ],
                    $this->repgeneratorStubService->getStub('ModelRelation')
                );
            }

        }

        $fillableStr = [];
        foreach ($columns as $column) {
            if ($column->fileUploadLocation) {
                continue;
            }
            $fillableStr[] = "'".$column->name."',";
        }

        $modelTemplate = str_replace(
            [
                '{{modelName}}',
                '{{use}}',
                '{{relation}}',
                '{{fillableFields}}'
            ],
            [
                $name,
                $this->implodeLines($use, 0),
                $this->implodeLines($relationTemplate, 2),
                $this->implodeLines($fillableStr, 2)
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
     * @param  string  $version
     * @param  string  $name
     * @param  bool  $readOnly
     * @param  array|null  $uploadsFiles
     * @param  bool  $isGeneratedFileDomain
     */
    private function apiController(string $version, string $name, bool $readOnly = false, array $uploadsFiles = null, bool $isGeneratedFileDomain = false)
    {
        $use = '';
        $filesRelation = '';

        if ($readOnly) {
            $stub = $this->repgeneratorStubService->getStub('ApiControllerReadOnly');
        } else {
            $stub = $this->repgeneratorStubService->getStub('ApiControllerReadWrite');
        }

        if (!empty($uploadsFiles) && !$isGeneratedFileDomain) {
            $use .= "use Illuminate\Http\JsonResponse;\n";
            $use .= "use Illuminate\Http\Request;\n";

            $filesRelation = $this->repgeneratorStubService->getStub('withFilesRelation');
        }

        $apiControllerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{use}}',
                '{{files}}',
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
                $use,
                $filesRelation,
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
     * @param  array  $columns
     */
    private function request(string $name, array $columns)
    {
        $requestTemplate = str_replace(
            [
                '{{modelName}}',
                '{{rules}}'
            ],
            [
                $name,
                $this->implodeLines($this->rulesByColumns($columns), 2)
            ],
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
     * @param  array  $columns
     * @return array
     */
    private function rulesByColumns(array $columns): array
    {
        $rules = [];
        foreach ($columns as $column) {
            $rule = '';
            if ($column->showOnTable) {
                $length = '';
                if ($column->length) {
                    $length = '|max:'.$column->length;
                }

                $rule .= match ($column->type) {
                    'binary', 'char', 'geometryCollection', 'geometry', 'ipAddress', 'rememberToken', 'set', 'softDeletes', 'uuidMorphs', 'uuid', 'text', 'json', 'jsonb', 'lineString', 'longText', 'macAddress', 'mediumText', 'multiLineString', 'multiPoint', 'multiPolygon', 'point', 'polygon', 'tinyText', 'string' => 'string'.$length,
                    'enum' => 'enum',
                    'boolean' => 'boolean',
                    'id', 'integer', 'bigIncrements', 'bigInteger', 'double', 'float', 'decimal', 'increments', 'mediumIncrements', 'mediumInteger', 'smallIncrements', 'smallInteger', 'tinyIncrements', 'tinyInteger', 'unsignedBigInteger', 'unsignedDecimal',
                    'unsignedInteger', 'unsignedMediumInteger', 'unsignedSmallInteger', 'unsignedTinyInteger' => 'integer',
                    'time', 'timestamp', 'timestamps', 'dateTime', 'date', 'year', 'nullableTimestamps' => 'date',
                    'softDeletesTz', 'dateTimeTz', 'timeTz', 'timestampTz', 'timestampsTz' => 'timezone'
                };
                $rule .= '|';
                if (!$column->nullable) {
                    $rule .= 'required';
                } else {
                    $rule .= 'nullable';
                }
                $rules[] = "'$column->name' => '$rule',";
            }
        }

        return $rules;
    }

    /**
     * @param  string  $name
     * @param  array  $columns
     */
    private function updateRequest(string $name, array $columns)
    {
        $updateRequestTemplate = str_replace(
            [
                '{{modelName}}',
                '{{rules}}'
            ],
            [
                $name,
                $this->implodeLines($this->rulesByColumns($columns), 2)
            ],
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
     * @param  string  $name
     * @param  bool  $generatePivot
     * @param  array|null  $uploadsFiles
     * @param  bool  $isGeneratedFileDomain
     */
    private function repositoryService(string $name, bool $generatePivot, array $uploadsFiles = null, bool $isGeneratedFileDomain = false)
    {
        $use = '';
        $traits = '';
        $saveOtherDataMethod = '';

        if (!empty($uploadsFiles) && !$isGeneratedFileDomain) {
            $use .= "use Illuminate\Database\Eloquent\Model;\n";
            $use .= "use App\\Domain\\".$name."File\\Repositories\\".$name."FileRepositoryService;\n";

            $saveOtherDataMethod = str_replace(
                [
                    '{{field}}',
                    '{{uploaderClass}}',
                ],
                [
                    strtolower($uploadsFiles['field']),
                    "{$name}FileRepositoryService::class"
                ],
                $this->repgeneratorStubService->getStub('saveOtherDataMethod')
            );
        }

        if($isGeneratedFileDomain) {
            $use .= "use App\Abstraction\Traits\UploadsFiles;\n";
            $traits .= "use UploadsFiles;\n";

        }

        $eloquentTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelType}}',
                '{{use}}',
                '{{traits}}',
                '{{saveOtherDataMethod}}',
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                strtolower($name),
                $generatePivot ? 'Pivot' : 'Model',
                $use,
                $traits,
                $saveOtherDataMethod
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
                $generatePivot ? 'Pivot' : 'Model',
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
     * @param  array|null  $uploadsFiles
     * @param  bool  $isGeneratedFileDomain
     */
    private function provider(string $name, bool $isPivot = false, array $uploadsFiles = null, bool $isGeneratedFileDomain = false)
    {
        $serviceSetters = "";
        if ($isGeneratedFileDomain) {
            $path = strtolower($name).'/'.$uploadsFiles['path'];
            $serviceSetters .= "->setFilesLocation('$path')";
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
     * @param  array  $foreigns
     * @param  bool  $isGeneratedFileDomain
     */
    private function resource(string $name, array $columns, array $foreigns, bool $isGeneratedFileDomain = false)
    {
        $routeName = strtolower(Str::plural($name));

        $actions = ['index', 'store', 'update', 'show', 'destroy'];

        $traits[] = "";
        $lines[] = "'actions' => [";
        foreach ($actions as $route) {
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

        $uses = [];
        foreach ($columns as $column) {
            if ($column->references) {
                $referenceSingular = Str::lcfirst(Str::studly(Str::singular($column->references['name'])));
                $referenceName = ucfirst($referenceSingular);
                $uses[] = "use App\Domain\\".$referenceName."\\Resources\\".$referenceName."Resource;\n";

                $resourceElementTemplate = str_replace(
                    [
                        '{{field}}',
                        '{{referenceSingular}}',
                        '{{referenceName}}',
                        '{{resourceMethod}}'
                    ],
                    [
                        $column->name,
                        $referenceSingular,
                        $referenceName,
                        'make'
                    ], $this->repgeneratorStubService->getStub('resourceElementRelation'));
            } elseif ($column->fileUploadLocation) {
                $uses[] = "use App\Domain\\".$name."File\\Resources\\".$name."FileResource;\n";

                $resourceElementTemplate = str_replace(
                    [
                        '{{field}}',
                        '{{referenceSingular}}',
                        '{{referenceName}}',
                        '{{resourceMethod}}'
                    ],
                    [
                        $column->name,
                        'files',
                        $name."File",
                        'collection'
                    ], $this->repgeneratorStubService->getStub('resourceElementRelation'));
            } elseif($column->type === 'date' || $column->type === 'dateTime') {
                if(!in_array("use Illuminate\Support\Carbon;\n", $uses)) {
                    $uses[] = "use Illuminate\Support\Carbon;\n";
                }

                if(!in_array("use Illuminate\Support\Facades\App;\n", $uses)) {
                    $uses[] = "use Illuminate\Support\Facades\App;\n";
                }

                $resourceElementTemplate = str_replace(
                    [
                        '{{field}}'
                    ],
                    [
                        $column->name
                    ],
                    $this->repgeneratorStubService->getStub('resourceElement'.ucfirst($column->type)));
            } else {
                $resourceElementTemplate = str_replace(
                    [
                        '{{field}}'
                    ],
                    [
                        $column->name
                    ],
                    $this->repgeneratorStubService->getStub('resourceElement'));
            }
            $lines[] = Constants::TAB.Constants::TAB.$resourceElementTemplate;
        }

        if($isGeneratedFileDomain) {
            $uses[] = "use App\\Domain\\".$name."\\Repositories\\".$name."RepositoryService;\n";
            $fileRepositoryClass = $name."RepositoryService::class";

            $relatedTableName = strtolower(Str::singular($foreigns[0]['reference']['name']));
            $resourceElementFileUrlTemplate = str_replace(
                [
                    '{{fileRepositoryClass}}',
                    '{{relation}}'
                ],
                [
                    $fileRepositoryClass,
                    $relatedTableName
                ],
                $this->repgeneratorStubService->getStub('resourceElementFileUrl'));

            $lines[] = $resourceElementFileUrlTemplate;
        }

        $resourceTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelResourceArray}}',
                '{{uses}}',
                '{{traits}}'
            ],
            [
                $name,
                $this->implodeLines($lines, 2),
                $this->implodeLines($uses, 2),
                $this->implodeLines($traits, 2),
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
     * @param  array  $foreigns
     * @param $callback
     */
    private function filters(string $name, array $columns, array $foreigns, $callback)
    {
        $this->generatedFiles[] = $this->repgeneratorFilterService->generate($name, $columns, $foreigns);
        $callback('Filter is ready!');
    }

    /**
     * @param  string  $name
     * @param  array  $columns
     * @param $callback
     */
    private function frontend(string $name, array $columns, $callback)
    {
        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateIndex($name, $columns);
        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateComposable($name, $columns);
        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateCreate($name, $columns);
        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateEdit($name, $columns);

        $callback('Frontend components are ready!');
    }

    /**
     * @param  mixed  $cmd
     */
    public function setCmd(string $cmd): void
    {
        $this->cmd = $cmd;
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
        foreach ($columns as $data) {
            $columnFactoriesString .= $data->name.' => $this->faker->';
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
}
