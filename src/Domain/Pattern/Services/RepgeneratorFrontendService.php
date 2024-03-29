<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Services;

use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;
use Pentacom\Repgenerator\Domain\Pattern\Adapters\RepgeneratorColumnAdapter;
use Pentacom\Repgenerator\Domain\Pattern\Helpers\CharacterCounterStore;
use Pentacom\Repgenerator\Traits\Stringable;

class RepgeneratorFrontendService
{
    use Stringable;

    /**
     * @param  RepgeneratorStubService  $repgeneratorStubService
     * @param  RepgeneratorNameTransformerService  $nameTransformerService
     */
    public function __construct(protected RepgeneratorStubService $repgeneratorStubService, protected RepgeneratorNameTransformerService $nameTransformerService)
    {

    }

    /**
     * @param string $chosenOutputFramework
     * @param string $name
     * @param array $columns
     * @return array
     */
    #[ArrayShape(['name' => "string", 'location' => "mixed"])] public function generateComposable(string $chosenOutputFramework, string $name, array $columns): array
    {
        $this->nameTransformerService->setModelName($name);
        $columnsConfig = [];
        /** @var RepgeneratorColumnAdapter $column */
        foreach ( $columns as $column ) {
            if ( in_array($column->name, ['id','created_at','updated_at']) ) {
                continue;
            }
            $columnProperties = [
                'name' => ucfirst($column->name),
                'required' => $column->nullable != true,
            ];
            if ( $column->type == 'boolean' ) {
                $columnProperties['isCheckbox'] = true;
            }
            if ($column->is_file) {
                $columnProperties['isFileUpload'] = true;
            }
            if ($column->is_picture) {
                $columnProperties['isPictureUpload'] = true;
            }
            $columnsConfig[$column->name] = $columnProperties;
        }

        $stub = $this->repgeneratorStubService->getStub('Frontend/Vue/composables/useModel');
        $frontendReplacer = app(RepgeneratorFrontendFrameworkHandlerService::class);
        $stub = $frontendReplacer->replaceForFramework($chosenOutputFramework, $stub);

        $composableTemplate = str_replace(
            [
                '{{ modelNamePluralLowerCaseHyphenated }}',
                '{{ columns }}',
                '{{ modelNamePluralUcfirst }}',
                '{{ modelNameSingularUcfirst }}',
                '{{ modelNamePluralLowercase }}',
            ],
            [
                $this->nameTransformerService->getModelNamePluralLowerCaseHyphenated(),
                json_encode($columnsConfig),
                $this->nameTransformerService->getModelNamePluralUcfirst(),
                $this->nameTransformerService->getModelNameSingularUcfirst(),
                $this->nameTransformerService->getModelNamePluralLowerCase(),
            ],
            $stub
        );

        $finalPath = "js".DIRECTORY_SEPARATOR."Domain".DIRECTORY_SEPARATOR."$name".DIRECTORY_SEPARATOR."composables".DIRECTORY_SEPARATOR."use" . $this->nameTransformerService->getModelNamePluralUcfirst() . ".ts";
        $pathParts = explode(DIRECTORY_SEPARATOR, $finalPath);
        foreach ( $pathParts as $index => $pathPart ) {
            if ( end($pathParts) == $pathPart ) {
                continue;
            }
            $partsSoFar = implode(DIRECTORY_SEPARATOR ,array_slice($pathParts,0, $index+1));
            if ( !is_dir(resource_path( $partsSoFar))) {
                mkdir(resource_path($partsSoFar), 0777, true);
            }
        }

        file_put_contents($path = resource_path($finalPath), $composableTemplate);

        CharacterCounterStore::addFileCharacterCount($path);

        return [
            'name' => "{$name}.js",
            'location' => $path
        ];
    }

    /**
     * @param  string  $chosenOutputFramework
     * @param  string  $name
     * @param  array  $columns
     * @return array
     */
    #[ArrayShape(['name' => "string", 'location' => "mixed"])] public function generateComponents(string $chosenOutputFramework, string $name, array $columns): array
    {
        $this->nameTransformerService->setModelName($name);
        $stub =  $this->repgeneratorStubService->getStub('Frontend/Vue/components/create');
        $frontendReplacer = app(RepgeneratorFrontendFrameworkHandlerService::class);
        $stub = $frontendReplacer->replaceForFramework($chosenOutputFramework, $stub);

        $createTemplate = str_replace(
            [
                '{{ modelNamePluralUcfirst }}',
                '{{ modelNameSingularUcfirst }}',
                '{{ modelNamePluralLowercase }}',
            ],
            [
                $this->nameTransformerService->getModelNamePluralUcfirst(),
                $this->nameTransformerService->getModelNameSingularUcfirst(),
                $this->nameTransformerService->getModelNamePluralLowerCase(),
            ],
            $stub
        );

        $editTemplate = str_replace(
            [
                '{{ modelNamePluralUcfirst }}',
                '{{ modelNameSingularUcfirst }}',
                '{{ modelNamePluralLowercase }}',
            ],
            [
                $this->nameTransformerService->getModelNamePluralUcfirst(),
                $this->nameTransformerService->getModelNameSingularUcfirst(),
                $this->nameTransformerService->getModelNamePluralLowerCase(),
            ],
            $this->repgeneratorStubService->getStub('Frontend/Vue/components/edit')
        );

        //Column handling for Index.vue
        $columnsTemplate = [];
        /** @var RepgeneratorColumnAdapter $column */
        foreach ($columns as $column) {
            if($column->showOnTable) {
                $nameParts = explode('_', $column->name);
                foreach ($nameParts as $index => $namePart) {
                    $nameParts[$index] = ucfirst(strtolower($namePart));
                }
                $field = implode(' ', $nameParts);

                $booleanTemplate = '';
                if($column->type === 'boolean') {
                    $booleanTemplate = str_replace(
                        ['{{ column }}'], [$column->name],
                        $this->repgeneratorStubService->getStub('Frontend/Vue/components/columnStubs/boolean')
                    );
                }

                $dateTemplate = match ($column->type) {
                    'dateTime', 'dateTimeTz', 'date', 'softDeletes', 'softDeletesTz' =>  $this->repgeneratorStubService->getStub('Frontend/Vue/components/columnStubs/date'),
                    default => ''
                };

                $columnsTemplate[] = str_replace(
                    [
                        '{{ column }}',
                        '{{ field }}',
                        '{{ booleanColumn }}',
                        '{{ dateColumn }}',
                    ],
                    [
                        $column->name,
                        $field,
                        $booleanTemplate,
                        $dateTemplate,
                    ],
                    $this->repgeneratorStubService->getStub('Frontend/Vue/components/columnStubs/column')
                );
            }
        }


        $indexTemplate = str_replace(
            [
                '{{ modelNamePluralLowerCaseHyphenated }}',
                '{{ modelNameSingularLowercase }}',
                '{{ modelNamePluralLowercase }}',
                '{{ modelNamePluralUcfirst }}',
                '{{ columns }}'
            ],
            [
                $this->nameTransformerService->getModelNamePluralLowerCaseHyphenated(),
                $this->nameTransformerService->getModelNameSingularLowerCase(),
                $this->nameTransformerService->getModelNamePluralLowerCase(),
                $this->nameTransformerService->getModelNamePluralUcfirst(),
                $this->implodeLines($columnsTemplate, 2)
            ],
            $this->repgeneratorStubService->getStub('Frontend/Vue/components/index')
        );

        $modelNameSingularUcfirst = $this->nameTransformerService->getModelNameSingularUcfirst();
        $files = [
            $modelNameSingularUcfirst . 'Create.vue' => $createTemplate,
            $modelNameSingularUcfirst. 'Edit.vue' => $editTemplate,
            $modelNameSingularUcfirst. 'Index.vue' => $indexTemplate,
        ];

        foreach ( $files as $file => $template ) {
            $finalPath = "js".DIRECTORY_SEPARATOR."Domain".DIRECTORY_SEPARATOR."$name".DIRECTORY_SEPARATOR."components".DIRECTORY_SEPARATOR. $file;
            $pathParts = explode(DIRECTORY_SEPARATOR, $finalPath);
            foreach ( $pathParts as $index => $pathPart ) {
                if ( end($pathParts) == $pathPart ) {
                    continue;
                }
                $partsSoFar = implode(DIRECTORY_SEPARATOR , array_slice($pathParts,0, $index+1));
                if ( !is_dir(resource_path( $partsSoFar))) {
                    mkdir(resource_path($partsSoFar), 0777, true);
                }
            }
            file_put_contents($path = resource_path($finalPath), $template);
        }


        CharacterCounterStore::addFileCharacterCount($path);

        return [
            'name' => "{$name}.js",
            'location' => $path
        ];
    }


    /**
     * @param string $name
     * @param string $icon
     * @return array
     */
    #[ArrayShape(['name' => "string", 'location' => "mixed"])] public function generatePages(string $name, string $icon): array
    {
        $this->nameTransformerService->setModelName($name);

        $createTemplate = str_replace(
            [
                '{{modelNamePluralUcfirst}}',
                '{{modelNameSingularUcfirst}}',
                '{{modelNamePluralLowercase}}',
                '{{modelNameSingularLowercase}}',
                '{{modelNamePluralLowerCaseHyphenated}}',
                '{{modelIcon}}',
            ],
            [
                $this->nameTransformerService->getModelNamePluralUcfirst(),
                $this->nameTransformerService->getModelNameSingularUcfirst(),
                $this->nameTransformerService->getModelNamePluralLowerCase(),
                $this->nameTransformerService->getModelNameSingularLowerCase(),
                $this->nameTransformerService->getModelNamePluralLowerCaseHyphenated(),
                $icon
            ],
            $this->repgeneratorStubService->getStub('Frontend/Vue/pages/create')
        );
        $editTemplate = str_replace(
            [
                '{{modelNamePluralUcfirst}}',
                '{{modelNameSingularUcfirst}}',
                '{{modelNamePluralLowercase}}',
                '{{modelNamePluralLowerCaseHyphenated}}',
                '{{modelIcon}}',
            ],
            [
                $this->nameTransformerService->getModelNamePluralUcfirst(),
                $this->nameTransformerService->getModelNameSingularUcfirst(),
                $this->nameTransformerService->getModelNamePluralLowerCase(),
                $this->nameTransformerService->getModelNamePluralLowerCaseHyphenated(),
                $icon
            ],
            $this->repgeneratorStubService->getStub('Frontend/Vue/pages/[id]')
        );
        $indexTemplate = str_replace(
            [
                '{{modelNamePluralUcfirst}}',
                '{{modelNameSingularLowercase}}',
                '{{modelNameSingularUcfirst}}',
                '{{modelNamePluralLowercase}}',
                '{{modelNamePluralLowerCaseHyphenated}}',
                '{{modelIcon}}',
            ],
            [
                $this->nameTransformerService->getModelNamePluralUcfirst(),
                $this->nameTransformerService->getModelNameSingularLowerCase(),
                $this->nameTransformerService->getModelNameSingularUcfirst(),
                $this->nameTransformerService->getModelNamePluralLowerCase(),
                $this->nameTransformerService->getModelNamePluralLowerCaseHyphenated(),
                $icon
            ],
            $this->repgeneratorStubService->getStub('Frontend/Vue/pages/index')
        );

        $files = [
            'create.vue' => $createTemplate,
            '[id].vue' => $editTemplate,
            'index.vue' => $indexTemplate,
        ];
        foreach ( $files as $file => $template ) {
            $finalPath = "js/Domain/$name/pages/" . $file;
            $pathParts = explode("/", $finalPath);
            foreach ( $pathParts as $index => $pathPart ) {
                if ( end($pathParts) == $pathPart ) {
                    continue;
                }
                $partsSoFar = implode('/',array_slice($pathParts,0, $index+1));
                if ( !is_dir(resource_path( $partsSoFar))) {
                    mkdir(resource_path($partsSoFar), 0777, true);
                }
            }
            file_put_contents($path = resource_path($finalPath), $template);
        }


        CharacterCounterStore::addFileCharacterCount($path);

        return [
            'name' => "{$name}.js",
            'location' => $path
        ];
    }


    /**
     * @return array
     */
    #[ArrayShape(['name' => "string", 'location' => "string"])] public function generateLarafetch(): array {
        $larafetchTemplate = str_replace(
            [
                '{{ backendUrl }}',
            ],
            [
                config('app.url'),
            ],
            $this->repgeneratorStubService->getStub('Frontend/Vue/utils/larafetch')
        );

        if (!file_exists($path = resource_path("js".DIRECTORY_SEPARATOR."Abstraction".DIRECTORY_SEPARATOR."utils".DIRECTORY_SEPARATOR))) {
            mkdir($path, 0777, true);
        }

        file_put_contents($path = resource_path("js".DIRECTORY_SEPARATOR."Abstraction".DIRECTORY_SEPARATOR."utils".DIRECTORY_SEPARATOR."\$larafetch.ts"),
            $larafetchTemplate);


        CharacterCounterStore::addFileCharacterCount($path);

        return [
            'name' => "\$larafetch.ts",
            'location' => $path
        ];
    }

    #[ArrayShape(['name' => "string", 'location' => "false|string"])] public function generateRoutesImports(string $name): array
    {
        $this->nameTransformerService->setModelName($name);
        $imports = [];
        $actions = ['Index', 'Create', 'Edit'];
        foreach ($actions as $action) {
            $imports[] = "import {$this->nameTransformerService->getModelNameSingularUcfirst()}{$action} from '../../Domain/{$name}/components/{$name}{$action}.vue'";
        }

        $routerPath = resource_path('js'.DIRECTORY_SEPARATOR.'Abstraction'.DIRECTORY_SEPARATOR.'router'.DIRECTORY_SEPARATOR.'router.js');
        $router = file_get_contents($routerPath);

        $lineEndingCount = [
            "\r\n" => substr_count($router, "\r\n"),
            "\r" => substr_count($router, "\r"),
            "\n" => substr_count($router, "\n"),
        ];

        $eol = array_keys($lineEndingCount, max($lineEndingCount))[0];

        foreach ($imports as $import) {
            $router = file_get_contents($routerPath);

            file_put_contents(
                $routerPath,
                str_replace(
                    '//DO NOT REMOVE THIS COMMENT BLOCK!! - IMPORTS SECTION'.$eol,
                    '//DO NOT REMOVE THIS COMMENT BLOCK!! - IMPORTS SECTION'.$eol."        $import".$eol,
                    $router
                ), LOCK_EX);
        }

        return [
            'name' => "router.js",
            'location' => "resources/js/Abstraction/router"
        ];
    }


    /**
     * @param  string  $name
     * @param  string  $crudUrlPrefix
     * @return string[]
     */
    #[ArrayShape(['name' => "string", 'location' => "string"])] public function generateRoutesBlock(string $name, string $crudUrlPrefix): array
    {
        $this->nameTransformerService->setModelName($name);

        $crudUrlPrefix = $crudUrlPrefix === '/' ? '' : $crudUrlPrefix;

        $routeBlockTemplate = str_replace(
            [
                '{{modelNamePluralLowerCase}}',
                '{{modelNameSingularUcfirst}}',
                '{{modelNamePluralLowerCaseHyphenated}}',
                '{{urlPrefix}}',
            ],
            [
                $this->nameTransformerService->getModelNamePluralLowerCase(),
                $this->nameTransformerService->getModelNameSingularUcfirst(),
                $this->nameTransformerService->getModelNamePluralLowerCaseHyphenated(),
                $crudUrlPrefix
            ],
            $this->repgeneratorStubService->getStub('Frontend/routeBlock')
        );

        $routerPath = resource_path('js'.DIRECTORY_SEPARATOR.'Abstraction'.DIRECTORY_SEPARATOR.'router'.DIRECTORY_SEPARATOR.'router.js');
        $router = file_get_contents($routerPath);

        $lineEndingCount = [
            "\r\n" => substr_count($router, "\r\n"),
            "\r" => substr_count($router, "\r"),
            "\n" => substr_count($router, "\n"),
        ];

        $eol = array_keys($lineEndingCount, max($lineEndingCount))[0];

        file_put_contents(
            $routerPath,
            str_replace(
                '//DO NOT REMOVE THIS COMMENT BLOCK!! - ROUTE SECTION'.$eol,
                '//DO NOT REMOVE THIS COMMENT BLOCK!! - ROUTE SECTION'.$eol."        $routeBlockTemplate".$eol,
                $router
            ), LOCK_EX);

        return [
            'name' => "router.js",
            'location' => "resources/js/Abstraction/router"
        ];
    }
}
