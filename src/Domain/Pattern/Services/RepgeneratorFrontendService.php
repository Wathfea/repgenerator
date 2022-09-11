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
        $columnsConfig = [];
        /** @var RepgeneratorColumnAdapter $column */
        foreach ( $columns as $column ) {
            if ( $column->name == 'id' ) {
                continue;
            }
            $columnProperties = [
                'name' => ucfirst($column->name),
                'required' => $column->nullable != true,
            ];
            if ( $column->type == 'boolean' ) {
                $columnProperties['isCheckbox'] = true;
            }
            $columnsConfig[] = $columnProperties;
        }

        $stub = $this->repgeneratorStubService->getStub('Frontend/Vue/composables/useModel');
        $frontendReplacer = app(RepgeneratorFrontendFrameworkHandlerService::class);
        $stub = $frontendReplacer->replaceForFramework($chosenOutputFramework, $stub);

        $composableTemplate = str_replace(
            [
                '{{ columns }}',
                '{{ modelNamePluralUcfirst }}',
                '{{ modelNameSingularUcfirst }}',
                '{{ modelNamePluralLowercase }}',
            ],
            [
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
                '{{ modelNameSingularLowercase }}',
                '{{ modelNamePluralLowercase }}',
                '{{ columns }}'
            ],
            [
                $this->nameTransformerService->getModelNameSingularLowerCase(),
                $this->nameTransformerService->getModelNamePluralLowerCase(),
                $this->implodeLines($columnsTemplate, 2)
            ],
            $this->repgeneratorStubService->getStub('Frontend/Vue/components/index')
        );

        $files = [
            'Create.vue' => $createTemplate,
            'Edit.vue' => $editTemplate,
            'Index.vue' => $indexTemplate,
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
                Str::snake(Str::plural($name), '-'),
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
                Str::snake(Str::plural($name), '-'),
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
                Str::snake(Str::plural($name), '-'),
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
        $imports = [];
        $actions = ['Index', 'Create', 'Edit'];
        foreach ($actions as $action) {
            $imports[] = "import {$this->nameTransformerService->getModelNamePluralUcfirst()}{$action} from '../Domain/{$name}/components/{$action}.vue'";
        }

        $router = file_get_contents(resource_path("js".DIRECTORY_SEPARATOR."Abstraction".DIRECTORY_SEPARATOR."router".DIRECTORY_SEPARATOR."router.js"));

        $lineEndingCount = [
            "\r\n" => substr_count($router, "\r\n"),
            "\r" => substr_count($router, "\r"),
            "\n" => substr_count($router, "\n"),
        ];

        $eol = array_keys($lineEndingCount, max($lineEndingCount))[0];

        foreach ($imports as $import) {
            file_put_contents(
                resource_path('js'.DIRECTORY_SEPARATOR.'Abstraction'.DIRECTORY_SEPARATOR.'router'.DIRECTORY_SEPARATOR.'router.js'),
                str_replace(
                    '//DO NOT REMOVE - IMPORTS SECTION'.$eol,
                    '//DO NOT REMOVE - IMPORTS SECTION'.$eol."        $import".$eol,
                    $router
                ));
        }

        return [
            'name' => "router.js",
            'location' => "resources/js/Abstraction/router"
        ];
    }

    /**
     * @return string[]
     */
    #[ArrayShape(['name' => "string", 'location' => "string"])] public function generateRoutesBlock(): array
    {
        $routeBlockTemplate = str_replace(
            [
                '{{modelNamePluralLowerCase}}',
                '{{modelNamePluralUcfirst}}',
            ],
            [
                $this->nameTransformerService->getModelNamePluralLowerCase(),
                $this->nameTransformerService->getModelNamePluralUcfirst(),
            ],
            $this->repgeneratorStubService->getStub('Frontend/routeBlock')
        );

        $router = file_get_contents(resource_path("js".DIRECTORY_SEPARATOR."Abstraction".DIRECTORY_SEPARATOR."router".DIRECTORY_SEPARATOR."router.js"));

        $lineEndingCount = [
            "\r\n" => substr_count($router, "\r\n"),
            "\r" => substr_count($router, "\r"),
            "\n" => substr_count($router, "\n"),
        ];

        $eol = array_keys($lineEndingCount, max($lineEndingCount))[0];

        file_put_contents(
            resource_path('js'.DIRECTORY_SEPARATOR.'Abstraction'.DIRECTORY_SEPARATOR.'router'.DIRECTORY_SEPARATOR.'router.js'),
            str_replace(
                '//DO NOT REMOVE - ROUTE SECTION'.$eol,
                '//DO NOT REMOVE - ROUTE SECTION'.$eol."        $routeBlockTemplate".$eol,
                $router
            ));

        return [
            'name' => "router.js",
            'location' => "resources/js/Abstraction/router"
        ];
    }
}
