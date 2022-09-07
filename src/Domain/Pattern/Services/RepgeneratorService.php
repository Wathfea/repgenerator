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
     * Model name should be ucfirst,no space,no special chars,singular E.g. Dog
     *
     * @var string
     */
    public string $modelName;

    /**
     * Lower case, plural version of $modelName E.g. dogs
     *
     * @var string
     */
    public string $modelNamePluralLowerCase;

    /**
     * Lower case, singular version of $modelName E.g. dog
     *
     * @var string
     */
    public string $modelNameSingularLowerCase;

    /**
     * @param  RepgeneratorStubService  $repgeneratorStubService
     * @param  RepgeneratorStaticFilesService  $repgeneratorStaticFilesService
     * @param  RepgeneratorFilterService  $repgeneratorFilterService
     * @param  RepgeneratorFrontendService  $repgeneratorFrontendService
     * @param  RepgeneratorNameTransformerService  $nameTransformerService
     */
    public function __construct(
        private RepgeneratorStubService $repgeneratorStubService,
        private RepgeneratorStaticFilesService $repgeneratorStaticFilesService,
        private RepgeneratorFilterService $repgeneratorFilterService,
        private RepgeneratorFrontendService $repgeneratorFrontendService,
        private RepgeneratorNameTransformerService $nameTransformerService
    ) {

    }

    /**
     * @param  string  $name
     * @return string|array
     */
    private function getSingularNameWithoutSpaces(string $name): string|array
    {
        return str_replace(' ', '', Str::singular($name));
    }

    /**
     * @param  string  $name
     * @return string|array
     */
    private function getPluralNameWithoutSpaces(string $name): string|array
    {
        return str_replace(' ', '',  $name);
    }


    /**
     * @param  array  $requestData
     * @param  array  $columns
     * @param  array  $foreigns
     * @param  array|null  $fileUploadFieldsData
     * @param  string|null  $migrationName
     * @param $callback
     * @param  bool  $isGenerateFrontend
     * @param  bool  $isGeneratedFileDomain
     * @return void
     */
    public function generate(
        array $requestData,
        array $columns,
        array $foreigns,
        $callback,
        array $fileUploadFieldsData = null,
        string $migrationName = null,
        bool $isGenerateFrontend = true,
        bool $isGeneratedFileDomain = false,
    ): void {
        //Setup names
        $this->nameTransformerService->setModelName($requestData['name']);
        $this->modelName = $this->nameTransformerService->getModelName();
        $this->modelNameSingularLowerCase = $this->nameTransformerService->getModelNameSingularLowerCase();
        $this->modelNamePluralLowerCase = $this->nameTransformerService->getModelNamePluralLowerCase();

        if (!empty($foreigns)) {
            $foreigns = collect($foreigns)->map(function ($foreign) {
                $this->nameTransformerService->setRelationName($foreign['reference']['name']);
                $this->nameTransformerService->setModelName($foreign['reference']['name']);

                if (!array_key_exists('relation_type', $foreign)) {
                    $foreign['relation_type'] = 'BelongsTo';
                    $foreign['related_model'] = $this->nameTransformerService->getModelName();
                    $foreign['relation_name'] = $this->nameTransformerService->getRelationMethodNameSingular();
                }

                if($foreign['relation_name'] === '') {
                    $foreign['relation_name'] = $this->nameTransformerService->getRelationMethodNameSingular();

                }

                return $foreign;
            })->toArray();
        }

        //Setup bools
        $isGeneratePivot = array_key_exists('pivot', $requestData) ? $requestData['pivot'] : false;
        $isReadOnly = array_key_exists('read_only', $requestData) ? $requestData['read_only'] : false;
        $isSoftDelete = array_key_exists('softDelete', $requestData) ? $requestData['softDelete'] : false;
        $isTimestamps = array_key_exists('timestamps', $requestData) ? $requestData['timestamps'] : false;

        $this->createDirectories();
        $callback('Directories generated!');


        $chosenOutputFramework = $requestData['chosen_output_framework'];
        $this->generateStaticFiles($chosenOutputFramework, $callback);

        $this->config($this->modelName, $requestData);
        $callback('Config is ready!');

        if ($isGeneratePivot) {
            $this->modelPivot($this->modelName);
        } else {
            $this->model($this->modelName, $columns, $foreigns, $isSoftDelete, $isTimestamps);
        }
        $callback('Model is ready!');

        $this->apiController('v1', $this->modelName, $isReadOnly, $isGeneratedFileDomain, $fileUploadFieldsData);
        $callback('API Controller is ready!');

        $this->request($this->modelName, $columns, $foreigns);
        $this->updateRequest($this->modelName, $columns, $foreigns);
        $callback('Controller requests are ready!');

        $this->repositoryService($this->modelName, $isGeneratePivot, $isGeneratedFileDomain, $fileUploadFieldsData);
        $callback('Repository layer is ready!');

        $this->service($this->modelName, $isGeneratePivot);
        $callback('Controller service is ready!');

        $this->provider($this->modelName, false, $columns, $isGeneratedFileDomain, $fileUploadFieldsData);
        $callback('Provider is ready!');

        $this->resource($this->modelName, $columns, $foreigns, $isGeneratedFileDomain);
        $callback('Resource is ready!');

        //$this->factory($name, $columns);
        //$callback('Factory is ready!');

        $this->filters($this->modelName, $columns, $foreigns, $callback);

        $this->apiRoutes($this->modelName);
        $callback('API Routes is ready!');


        !$isGenerateFrontend ?: $this->frontend2($chosenOutputFramework, $this->modelName, $columns, $callback);

        $callback("Code generation has saved you from typing at least ".CharacterCounterStore::$charsCount." characters");
        $minutes = floor((CharacterCounterStore::$charsCount / 5) / 25);
        $hours = floor($minutes / 60);

        $callback("If we count an average 5 char word and an average 25 WPM we saved you around {$minutes} minutes -> {$hours} hours");

        if ($migrationName) {
            if(class_exists(CrudMenuServiceProvider::class)) {
                app()->register(CrudMenuServiceProvider::class);
                Artisan::call('migrate',
                    [
                        '--path' => '/database/migrations/'.$migrationName,
                        '--force' => true
                    ]);
                $callback($migrationName.' migration migrated to database!');
            }
        }
    }

    /**
     * Create static file holder directories
     */
    private function createDirectories(): void
    {
        foreach ( [
                      "Abstraction",
                      "Abstraction/Filter",
                      "Abstraction/Models",
                      "Abstraction/Repository",
                      "Abstraction/Controllers",
                      "Abstraction/Enums",
                      "Abstraction/Traits",
                      "Domain",
                  ] as $folder ) {
            if (!file_exists($path = app_path($folder))) {
                mkdir($path, 0777, true);
            }
        };

        foreach ( [
            'js', 'js/Abstraction', 'js/Domain',
            'js/Abstraction/components', 'js/Abstraction/composables', 'js/Abstraction/utils'
        ] as $folder ) {
            if (!file_exists($path = resource_path($folder))) {
                mkdir($path, 0777, true);
            }
        };
    }

    /**
     * @param string $frontendFramework
     * @param $callback
     */
    private function generateStaticFiles(string $frontendFramework, $callback): void
    {
        $staticFiles = $this->repgeneratorStaticFilesService->copyStaticFiles();
        $staticFiles = array_merge($staticFiles, $this->repgeneratorStaticFilesService->copyStaticFrontendFiles($frontendFramework));
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
    private function modelPivot(string $name): void
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
     */
    private function apiRoutes(string $name): void
    {
        $apiRouteTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNamePluralLowerCaseHyphenated}}'
            ],
            [
                $name,
                strtolower(Str::plural($name)),
                Str::snake($name, '-')
            ],
            $this->repgeneratorStubService->getStub('apiRoutes')
        );

        if (!file_exists($path = app_path("Domain/{$name}/Routes/"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/Routes/api.php"), $apiRouteTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        $this->generatedFiles[] = [
            'name' => "api.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     * @param  array  $requestData
     */
    private function config(string $name, array $requestData): void
    {
        $configTemplate = str_replace(
            [
                '{{provider}}',
                '{{name}}',
                '{{meta}}',
            ],
            [
                "App\Domain\\".$name."\Providers\\".$name."ServiceProvider::class",
                $name,
                json_encode($requestData)
            ],
            $this->repgeneratorStubService->getStub('config')
        );

        if (!file_exists($path = app_path("Domain/{$name}/"))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = app_path("Domain/{$name}/config.php"), $configTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        $this->generatedFiles[] = [
            'name' => "config.php",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     * @param  array  $columns
     * @param  array  $foreigns
     * @param  bool  $isSoftDelete
     * @param  bool  $isTimestamps
     */
    private function model(string $name, array $columns, array $foreigns, bool $isSoftDelete, bool $isTimestamps): void
    {
        $use = [];
        $relationTemplate = [];
        $hashedTemplate = '';
        $cryptedTemplate = '';

        if (!empty($foreigns)) {
            foreach ($foreigns as $foreign) {
                $relationType = $foreign['relation_type'];
                $relatedModel = $foreign['related_model'];
                $relationName = $foreign['relation_name'];

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
                        '{{relatedModel}}',
                        '{{foreignKey}}',
                        '{{ownerKey}}',
                    ],
                    [
                        $relationType,
                        $relationName,
                        Str::camel($relationType),
                        $relatedModel,
                        $foreign['column'],
                        $foreign['on'],
                    ],
                    $this->repgeneratorStubService->getStub('ModelRelation')
                );
            }

        }

        $fillableStr = [];
        $columnConstants = [];
        foreach ($columns as $column) {
            if ($column->fileUploadLocation) {
                continue;
            }
            $fillableStr[] = "'".$column->name."',";
            if ( $column->name != 'id' ) {
                $columnConstants[] = 'const ' . Str::upper($column->name) . '_COLUMN = "' . $column->name . '";';
            }

            if($column->is_hashed) {
                if(!in_array('use Illuminate\Support\Facades\Hash;', $use)) {
                    $use[] = 'use Illuminate\Support\Facades\Hash;';
                }
                $hashedTemplate = str_replace(
                    [
                        '{{fieldUpper}}',
                        '{{field}}',
                    ],
                    [
                        ucfirst($column->name),
                        strtolower($column->name),
                    ],
                    $this->repgeneratorStubService->getStub('modelHashedField')
                );
            }

            if($column->is_crypted) {
                if(!in_array('use Illuminate\Support\Facades\Crypt;', $use)) {
                    $use[] = 'use Illuminate\Support\Facades\Crypt;';
                }
                $cryptedTemplate = str_replace(
                    [
                        '{{fieldUpper}}',
                        '{{field}}',
                    ],
                    [
                        ucfirst($column->name),
                        strtolower($column->name),
                    ],
                    $this->repgeneratorStubService->getStub('modelCryptedField')
                );
            }
        }

        $trait = '';
        if($isSoftDelete) {
            $use[] = 'use Illuminate\Database\Eloquent\SoftDeletes;';
            $trait = ', SoftDeletes';
        }

        $timestampsTemplate = '';
        if(!$isTimestamps) {
            $timestampsTemplate = 'public $timestamps = false;';
        }

        $modelTemplate = str_replace(
            [
                '{{modelName}}',
                '{{use}}',
                '{{relation}}',
                '{{fillableFields}}',
                '{{trait}}',
                '{{hashedTemplate}}',
                '{{cryptedTemplate}}',
                '{{timestamps}}',
                '{{columnConstants}}',
            ],
            [
                $name,
                $this->implodeLines($use, 0),
                $this->implodeLines($relationTemplate, 2),
                $this->implodeLines($fillableStr, 2),
                $trait,
                $hashedTemplate,
                $cryptedTemplate,
                $timestampsTemplate,
                $this->implodeLines($columnConstants, 1)
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
     * @param  bool  $isReadOnly
     * @param  bool  $isGeneratedFileDomain
     * @param  array|null  $fileUploadFieldsData
     */
    private function apiController(string $version, string $name, bool $isReadOnly, bool $isGeneratedFileDomain, array $fileUploadFieldsData = null): void
    {
        $use = [];
        $filesRelation = [];

        if ($isReadOnly) {
            $stub = $this->repgeneratorStubService->getStub('ApiControllerReadOnly');
        } else {
            $stub = $this->repgeneratorStubService->getStub('ApiControllerReadWrite');
        }

        if (!empty($fileUploadFieldsData) && !$isGeneratedFileDomain) {
            $use[] = "use Illuminate\Http\JsonResponse;\n";
            $use[] = "use Illuminate\Http\Request;\n";

            $filesRelation[] = $this->repgeneratorStubService->getStub('withFilesRelation');
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
                $this->modelNamePluralLowerCase,
                $this->modelNameSingularLowerCase,
                $this->implodeLines($use, 2),
                $this->implodeLines($filesRelation, 2),
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
     * @param  array  $foreigns
     */
    private function request(string $name, array $columns, array $foreigns): void
    {
        $requestTemplate = str_replace(
            [
                '{{modelName}}',
                '{{rules}}'
            ],
            [
                $name,
                $this->implodeLines($this->rulesByColumns($columns, $foreigns), 2)
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
     * @param  array  $foreigns
     * @return array
     */
    private function rulesByColumns(array $columns, array $foreigns): array
    {
        $rules = [];
        foreach ($foreigns as $foreign) {
            foreach ($columns as $column) {
                $rule = '';
                if ($column->showOnTable) {
                    $length = '';
                    if ($column->length) {
                        $length = '|max:'.$column->length;
                    }

                    if (!$column->fileUploadLocation) {
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
                    }

                    if (!$column->nullable) {
                        $rule .= 'required';
                    } else {
                        $rule .= 'nullable';
                    }

                    if($foreign['column'] === $column->name) {
                        $rule .= '|exists:'.$foreign['reference']['name'].','.$foreign['on'];
                    }

                    $rules[] = "'$column->name' => '$rule',";
                }
            }
        }

        return $rules;
    }

    /**
     * @param  string  $name
     * @param  array  $columns
     * @param  array  $foreigns
     */
    private function updateRequest(string $name, array $columns, array $foreigns): void
    {
        $updateRequestTemplate = str_replace(
            [
                '{{modelName}}',
                '{{rules}}'
            ],
            [
                $name,
                $this->implodeLines($this->rulesByColumns($columns, $foreigns), 2)
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
     * @param  bool  $isGeneratePivot
     * @param  bool  $isGeneratedFileDomain
     * @param  array|null  $fileUploadFieldsData
     */
    private function repositoryService(string $name, bool $isGeneratePivot, bool $isGeneratedFileDomain, array $fileUploadFieldsData = null): void
    {
        $use = [];
        $traits = [];
        $saveOtherDataMethod = [];

        if (!empty($fileUploadFieldsData) && !$isGeneratedFileDomain) {
            $use[] = "use Illuminate\Database\Eloquent\Model;\n";
            $use[] = "use App\\Domain\\".$name."File\\Repositories\\".$name."FileRepositoryService;\n";

            $fields = '[';
            foreach ($fileUploadFieldsData as $field) {
                $f[] = "'".strtolower($field['field'])."'";
            }
            $fields .= implode(",", $f);
            $fields .= ']';


            $saveOtherDataMethod[] = str_replace(
                [
                    '{{fields}}',
                    '{{uploaderClass}}',
                ],
                [
                    $fields,
                    "{$name}FileRepositoryService::class"
                ],
                $this->repgeneratorStubService->getStub('saveOtherDataMethod')
            );
        }

        if($isGeneratedFileDomain) {
            $use[] = "use App\Abstraction\Traits\UploadsFiles;\n";
            $traits[] = "use UploadsFiles;\n";

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
                $this->modelNamePluralLowerCase,
                $this->modelNameSingularLowerCase,
                $isGeneratePivot ? 'Pivot' : 'Model',
                $this->implodeLines($use, 2),
                $this->implodeLines($traits, 2),
                $this->implodeLines($saveOtherDataMethod, 2)
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
    private function service(string $name, bool $generatePivot): void
    {
        $code = '';

        $codeStubPath = 'codes/' . $name . 'Service';
        if ( $this->repgeneratorStubService->doesStubExist($codeStubPath) ) {
            $code = $this->repgeneratorStubService->getStub($codeStubPath);
        }

        $uses = [];
        if($name == 'CrudMenu') {
            $uses[] = "use App\Domain\CrudMenuGroup\Services\CrudMenuGroupService;";
            $uses[] = "use App\Domain\CrudMenu\Enums\CrudMenuGroupType;";
            $uses[] = "use App\Domain\CrudMenuGroup\Models\CrudMenuGroup;";
            $uses[] = "use App\Domain\CrudMenu\Models\CrudMenu;";
        }

        if($name == 'CrudMenuGroup') {
            $uses[] = "use App\Domain\CrudMenuGroup\Models\CrudMenuGroup;";
            $uses[] = "use App\Domain\CrudMenu\Enums\CrudMenuGroupType;";

        }

        $serviceTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelType}}',
                '{{code}}',
                '{{uses}}',
            ],
            [
                $name,
                $this->modelNamePluralLowerCase,
                $this->modelNameSingularLowerCase,
                $generatePivot ? 'Pivot' : 'Model',
                $code,
                $this->implodeLines($uses, 2),
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
     * @param  array  $columns
     * @param  bool  $isGeneratedFileDomain
     * @param  array|null  $fileUploadFieldsData
     */
    private function provider(string $name, bool $isPivot, array $columns, bool $isGeneratedFileDomain, array $fileUploadFieldsData = null): void
    {
        $serviceSetters = "";
        $f = [];
        if ($isGeneratedFileDomain) {

            $fieldsPath = '[';
            foreach ($fileUploadFieldsData as $field) {
                $path = strtolower($name).'/'.$field['path'];
                $f[] = "'".strtolower($field['field'])."' => '" . $path ."'";
            }
            $fieldsPath .= implode(',', $f);
            $fieldsPath .= ']';

            $serviceSetters .= "->setFilesLocation($fieldsPath)";
        }

        $searchables = '';
        $s = [];
        foreach ($columns as $column) {
            if($column->searchable) {
                $s[] = "'".strtolower($column->name)."'";
            }
        }
        $searchables .= implode(',', $s);


        $providerTemplate = str_replace(
            [
                '{{modelName}}',
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularLowerCase}}',
                '{{repoParams}}',
                '{{serviceSetters}}',
                '{{searchables}}',
            ],
            [
                $name,
                $this->modelNamePluralLowerCase,
                $this->modelNameSingularLowerCase,
                $isPivot ? 'TODO::class' : $name.'::class',
                $serviceSetters,
                $searchables
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
    private function resource(string $name, array $columns, array $foreigns, bool $isGeneratedFileDomain): void
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
            $resourceElementTemplate = "";

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
                if(!in_array("use App\Domain\\".$name."File\\Resources\\".$name."FileResource;\n", $uses)) {
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
                            $name.'File',
                            'collection'
                        ], $this->repgeneratorStubService->getStub('resourceElementRelation'));
                }
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
                $bool = '';
                if($column->type === 'boolean') {
                    $bool = '(bool)';
                }

                $resourceElementTemplate = str_replace(
                    [
                        '{{field}}',
                        '{{bool}}',
                    ],
                    [
                        $column->name,
                        $bool
                    ],
                    $this->repgeneratorStubService->getStub('resourceElement'));
            }

            if($resourceElementTemplate !== "") $lines[] = Constants::TAB.Constants::TAB.$resourceElementTemplate;
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
    private function filters(string $name, array $columns, array $foreigns, $callback): void
    {
        $this->generatedFiles[] = $this->repgeneratorFilterService->generate($name, $columns, $foreigns);
        $callback('Filter is ready!');
    }

    /**
     * @param  string  $name
     * @param  array  $columns
     * @param $callback
     */
    private function frontend(string $name, array $columns, $callback): void
    {
        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateIndex($name, $columns);
        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateComposable($name, $columns);
        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateCreate($name, $columns);
        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateEdit($name, $columns);

        $callback('Frontend components are ready!');
    }

    /**
     * @param string $chosenOutputFramework
     * @param string $name
     * @param array $columns
     * @param $callback
     */
    private function frontend2(string $chosenOutputFramework, string $name, array $columns, $callback): void
    {

        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateComposable2($chosenOutputFramework, $name, $columns);
        $this->generatedFiles[] = $this->repgeneratorFrontendService->generateComponents2($chosenOutputFramework, $name, $columns);

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
    private function factory(string $name, array $columns): void
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
