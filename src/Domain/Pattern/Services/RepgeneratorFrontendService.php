<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Services;

use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;
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
        $columns = '{}';

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
                $columns,
                $this->nameTransformerService->getModelNamePluralUcfirst(),
                $this->nameTransformerService->getModelNameSingularUcfirst(),
                $this->nameTransformerService->getModelNamePluralLowerCase(),
            ],
            $stub
        );

        $finalPath = "js/Domain/$name/composables/use" . $this->nameTransformerService->getModelNamePluralUcfirst() . ".ts";
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
        $indexTemplate = str_replace(
            [
                '{{ modelNameSingularLowercase }}',
                '{{ modelNamePluralLowercase }}',
            ],
            [
                $this->nameTransformerService->getModelNameSingularLowerCase(),
                $this->nameTransformerService->getModelNamePluralLowerCase(),
            ],
            $this->repgeneratorStubService->getStub('Frontend/Vue/components/index')
        );

        $files = [
            'Create.vue' => $createTemplate,
            'Edit.vue' => $editTemplate,
            'Index.vue' => $indexTemplate,
        ];
        foreach ( $files as $file => $template ) {
            $finalPath = "js/Domain/$name/components/" . $file;
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
}
