<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Services;

use Illuminate\Support\Str;
use Pentacom\Repgenerator\Domain\Pattern\Adapters\RepgeneratorColumnAdapter;
use Pentacom\Repgenerator\Domain\Pattern\Helpers\CharacterCounterStore;
use Pentacom\Repgenerator\Traits\Stringable;

class RepgeneratorFrontendService
{
    use Stringable;

    public function __construct(protected RepgeneratorStubService $repgeneratorStubService)
    {

    }

    /**
     * @param  string  $name
     * @param  array  $columns
     * @return array
     */
    public function generateComposable(string $name, array $columns): array
    {
        $imageLineTemplate = [];
        /**
         * @var  $column
         * @var  RepgeneratorColumnAdapter $data
         */
        foreach ($columns as $data) {
            if ($data->fileUploadLocation) {
                $imageLineTemplate[] = str_replace(
                    [
                        '{{modelNameSingularLowerCase}}',
                        '{{imageField}}',
                    ],
                    [
                        strtolower($name),
                        $data->name
                    ],
                    $this->repgeneratorStubService->getStub('Frontend/Vue/getMethodImageLine')
                );
            }
        }

        $composableTemplate = str_replace(
            [
                '{{modelNameSingular}}',
                '{{modelNamePlural}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{baseUrl}}',
                '{{getMethodImageLine}}',
            ],
            [
                $name,
                Str::plural($name),
                $lowerName = strtolower($name),
                Str::plural(strtolower($name)),
                '',
                $this->implodeLines($imageLineTemplate, 0)
            ],
            $this->repgeneratorStubService->getStub('Frontend/Vue/composable')
        );

        if (!file_exists($path = resource_path('js'))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = resource_path('js/'.$name))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = resource_path('js/'.$name.'/vue'))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = resource_path("js/{$name}/vue/{$lowerName}.js"), $composableTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        return [
            'name' => "{$name}.js",
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     * @param  array  $columns
     * @return array
     */
    public function generateCreate(string $name, array $columns): array
    {
        $createFormStr = [];
        $columnListStr = [];
        $imageFieldName = '';

        /**
         * @var  $column
         * @var  RepgeneratorColumnAdapter $data
         */
        foreach ($columns as $data) {
            if ($data->showOnTable) {
                $nameParts = explode('_', $data->name);
                foreach ($nameParts as $index => $namePart) {
                    $nameParts[$index] = ucfirst(strtolower($namePart));
                }
                $field = implode(' ', $nameParts);

                if ($data->fileUploadLocation) {
                    $template = 'inputFile';
                } else {
                    $template = match ($data->type) {
                        'id', 'integer', 'string', 'bigIncrements', 'bigInteger', 'binary', 'char', 'dateTimeTz', 'dateTime', 'date', 'decimal',
                        'double', 'float', 'geometryCollection', 'geometry', 'increments', 'ipAddress', 'mediumIncrements', 'mediumInteger', 'nullableTimestamps',
                        'rememberToken', 'set', 'smallIncrements', 'smallInteger', 'softDeletesTz', 'softDeletes', 'timeTz', 'time',
                        'timestampTz', 'timestamp', 'timestampsTz', 'timestamps', 'tinyIncrements', 'tinyInteger', 'unsignedBigInteger', 'unsignedDecimal',
                        'unsignedInteger', 'unsignedMediumInteger', 'unsignedSmallInteger', 'unsignedTinyInteger', 'uuidMorphs', 'uuid', 'year' => 'inputText',
                        'text', 'json', 'jsonb', 'lineString', 'longText', 'macAddress', 'mediumText', 'multiLineString',
                        'multiPoint', 'multiPolygon', 'point', 'polygon', 'tinyText' => 'inputTextarea',
                        'enum' => 'inputOption',
                        'boolean' => 'inputCheckbox',
                    };
                }


                if ($template == 'inputOption') {
                    $selectOptions = [];
                    foreach ($data->values as $option) {
                        if ($option == '' || $option == null) {
                            continue;
                        }
                        $selectOptions[] = str_replace(
                            [
                                '{{optionValue}}',
                                '{{optionName}}'
                            ],
                            [
                                $option,
                                ucfirst(strtolower($option))
                            ],
                            $this->repgeneratorStubService->getStub('Frontend/Vue/fields/'.$template)
                        );
                    }

                    $createFormStr[] = str_replace(
                        [
                            '{{field}}',
                            '{{fieldLower}}',
                            '{{options}}',
                            '{{modelNameSingularLowerCase}}',
                        ],
                        [
                            $field,
                            $data->name,
                            $this->implodeLines($selectOptions, 2),
                            strtolower($name)
                        ],
                        $this->repgeneratorStubService->getStub('Frontend/Vue/fields/inputSelect')
                    );
                } elseif ($template == 'inputFile') {
                    $createFormStr[] = str_replace(
                        [
                            '{{field}}',
                            '{{fieldLower}}',
                            '{{modelNameSingularLowerCase}}',
                        ],
                        [
                            $field,
                            $data->name,
                            strtolower($name)
                        ],
                        $this->repgeneratorStubService->getStub('Frontend/Vue/fields/inputFile')
                    );
                } else {
                    $createFormStr[] = str_replace(
                        [
                            '{{field}}',
                            '{{fieldLower}}',
                            '{{modelNameSingularLowerCase}}',
                        ],
                        [
                            $field,
                            $data->name,
                            strtolower($name)
                        ],
                        $this->repgeneratorStubService->getStub('Frontend/Vue/fields/'.$template)
                    );
                }
                //Create column list
                if ($data->fileUploadLocation) {
                    $imageFieldName = $data->name;
                    $columnListStr[] = $data->name.": [],";
                } elseif ($data->type === 'boolean') {
                    $columnListStr[] = $data->name.": $data->default,";
                } else {
                    $columnListStr[] = $data->name.": '',";
                }
            }
        }

        $templateWithFileUpload = $imageFieldName === '' ? 'create' : 'createWithFileUpload';

        $createTemplate = str_replace(
            [
                '{{modelNameSingular}}',
                '{{modelNamePlural}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{form}}',
                '{{columnList}}',
                '{{imageFieldName}}'
            ],
            [
                $name,
                Str::plural($name),
                strtolower($name),
                Str::plural(strtolower($name)),
                $this->implodeLines($createFormStr, 2),
                $this->implodeLines($columnListStr, 2),
                $imageFieldName
            ],
            $this->repgeneratorStubService->getStub('Frontend/Vue/'.$templateWithFileUpload)
        );

        if (!file_exists($path = resource_path('js'))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = resource_path('js/'.$name))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = resource_path('js/'.$name.'/vue'))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = resource_path("js/{$name}/vue/create.vue"), $createTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        return [
            'name' => 'create.vue',
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     * @param  array  $columns
     * @return array
     */
    public function generateEdit(string $name, array $columns): array
    {
        $editFormStr = [];
        $columnListStr = [];
        $imageFieldName = '';

        /**
         * @var  $column
         * @var  RepgeneratorColumnAdapter $data
         */
        foreach ($columns as $data) {
            if ($data->showOnTable) {
                $nameParts = explode('_', $data->name);
                foreach ($nameParts as $index => $namePart) {
                    $nameParts[$index] = ucfirst(strtolower($namePart));
                }
                $field = implode(' ', $nameParts);

                if ($data->fileUploadLocation && $data->is_file) {
                    $template = 'inputFile';
                } elseif ($data->fileUploadLocation && $data->is_picture) {
                    $template = 'inputPicture';
                } else {
                    $template = match ($data->type) {
                        'id', 'integer', 'string', 'bigIncrements', 'bigInteger', 'binary', 'char', 'dateTimeTz', 'dateTime', 'date', 'decimal',
                        'double', 'float', 'geometryCollection', 'geometry', 'increments', 'ipAddress', 'mediumIncrements', 'mediumInteger', 'nullableTimestamps',
                        'rememberToken', 'set', 'smallIncrements', 'smallInteger', 'softDeletesTz', 'softDeletes', 'timeTz', 'time',
                        'timestampTz', 'timestamp', 'timestampsTz', 'timestamps', 'tinyIncrements', 'tinyInteger', 'unsignedBigInteger', 'unsignedDecimal',
                        'unsignedInteger', 'unsignedMediumInteger', 'unsignedSmallInteger', 'unsignedTinyInteger', 'uuidMorphs', 'uuid', 'year' => 'inputText',
                        'text', 'json', 'jsonb', 'lineString', 'longText', 'macAddress', 'mediumText', 'multiLineString',
                        'multiPoint', 'multiPolygon', 'point', 'polygon', 'tinyText' => 'inputTextarea',
                        'enum' => 'inputOption',
                        'boolean' => 'inputCheckbox',
                    };
                }


                if ($template == 'inputOption') {
                    $selectOptions = [];
                    foreach ($data->values as $option) {
                        if ($option == '' || $option == null) {
                            continue;
                        }
                        $selectOptions[] = str_replace(
                            [
                                '{{optionValue}}',
                                '{{optionName}}'
                            ],
                            [
                                $option,
                                ucfirst(strtolower($option))
                            ],
                            $this->repgeneratorStubService->getStub('Frontend/Vue/fields/'.$template)
                        );
                    }

                    $editFormStr[] = str_replace(
                        [
                            '{{field}}',
                            '{{fieldLower}}',
                            '{{options}}',
                            '{{modelNameSingularLowerCase}}',
                        ],
                        [
                            $field,
                            $data->name,
                            $this->implodeLines($selectOptions, 2),
                            strtolower($name)
                        ],
                        $this->repgeneratorStubService->getStub('Frontend/Vue/fields/inputSelect')
                    );
                } elseif ($template == 'inputPicture') {
                    $editFormStr[] = str_replace(
                        [
                            '{{field}}',
                            '{{modelNameSingularLowerCase}}',
                        ],
                        [
                            strtolower($field),
                            strtolower($name)
                        ],
                        $this->repgeneratorStubService->getStub('Frontend/Vue/fields/picture')
                    );

                    $editFormStr[] = str_replace(
                        [
                            '{{field}}',
                            '{{fieldLower}}',
                            '{{modelNameSingularLowerCase}}',
                        ],
                        [
                            $field,
                            $data->name,
                            strtolower($name)
                        ],
                        $this->repgeneratorStubService->getStub('Frontend/Vue/fields/inputFile')
                    );
                } elseif ($template == 'inputFile') {
                    $editFormStr[] = str_replace(
                        [
                            '{{field}}',
                            '{{modelNameSingularLowerCase}}',
                        ],
                        [
                            strtolower($field),
                            strtolower($name)
                        ],
                        $this->repgeneratorStubService->getStub('Frontend/Vue/fields/file')
                    );

                    $editFormStr[] = str_replace(
                        [
                            '{{field}}',
                            '{{fieldLower}}',
                            '{{modelNameSingularLowerCase}}',
                        ],
                        [
                            $field,
                            $data->name,
                            strtolower($name)
                        ],
                        $this->repgeneratorStubService->getStub('Frontend/Vue/fields/inputFile')
                    );
                }
                else {
                    $editFormStr[] = str_replace(
                        [
                            '{{field}}',
                            '{{fieldLower}}',
                            '{{modelNameSingularLowerCase}}',
                        ],
                        [
                            $field,
                            $data->name,
                            strtolower($name)
                        ],
                        $this->repgeneratorStubService->getStub('Frontend/Vue/fields/'.$template)
                    );
                }
                //Create column list
                if ($data->fileUploadLocation) {
                    $imageFieldName = $data->name;
                }

                if ($data->type === 'boolean') {
                    $columnListStr[] = $data->name.": $data->default,";
                } else {
                    $columnListStr[] = $data->name.": '',";
                }
            }
        }

        $templateWithFileUpload = $data->fileUploadLocation === null ? 'edit' : 'editWithFileUpload';

        $createTemplate = str_replace(
            [
                '{{modelNameSingular}}',
                '{{modelNamePlural}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{form}}',
                '{{columnList}}',
                '{{imageFieldName}}'
            ],
            [
                $name,
                Str::plural($name),
                strtolower($name),
                Str::plural(strtolower($name)),
                $this->implodeLines($editFormStr, 2),
                $this->implodeLines($columnListStr, 2),
                $imageFieldName
            ],
            $this->repgeneratorStubService->getStub('Frontend/Vue/'.$templateWithFileUpload)
        );

        if (!file_exists($path = resource_path('js'))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = resource_path('js/'.$name))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = resource_path('js/'.$name.'/vue'))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = resource_path("js/{$name}/vue/edit.vue"), $createTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        return [
            'name' => 'edit.vue',
            'location' => $path
        ];
    }

    /**
     * @param  string  $name
     * @param  array  $columns
     * @return array
     */
    public function generateIndex(string $name, array $columns): array
    {
        $columnsToShowOnTable = [];
        /**
         * @var  $column
         * @var  RepgeneratorColumnAdapter $data
         */
        foreach ($columns as $data) {
            if ($data->showOnTable && $data->fileUploadLocation === null) {
                $nameParts = explode('_', $data->name);
                foreach ($nameParts as $index => $namePart) {
                    $nameParts[$index] = ucfirst(strtolower($namePart));
                }
                $columnsToShowOnTable[implode(' ', $nameParts)] = $data->name;
            }
        }
        $indexTemplate = str_replace(
            [
                '{{modelNameSingular}}',
                '{{modelNamePlural}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{baseUrl}}',
                '{{modelColumns}}'
            ],
            [
                $name,
                Str::plural($name),
                strtolower($name),
                Str::plural(strtolower($name)),
                url(''),
                json_encode($columnsToShowOnTable)
            ],
            $this->repgeneratorStubService->getStub('Frontend/Vue/index')
        );

        if (!file_exists($path = resource_path('js'))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = resource_path('js/'.$name))) {
            mkdir($path, 0777, true);
        }

        if (!file_exists($path = resource_path('js/'.$name.'/vue'))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = resource_path("js/{$name}/vue/index.vue"), $indexTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        return [
            'name' => "index.vue",
            'location' => $path
        ];
    }
}
