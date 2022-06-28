<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Services;

use Illuminate\Support\Str;
use Pentacom\Repgenerator\Domain\Pattern\Adapters\RepgeneratorColumnAdapter;
use Pentacom\Repgenerator\Domain\Pattern\Helpers\CharacterCounterStore;

class RepgeneratorFrontendService
{
    public function __construct(protected RepgeneratorStubService $repgeneratorStubService) {

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
            if ($data->showOnTable) {
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

    /**
     * @param string $name
     * @return array
     */
    public function generateComposable(string $name): array
    {
        $composableTemplate = str_replace(
            [
                '{{modelNameSingular}}',
                '{{modelNamePlural}}',
                '{{modelNameSingularLowerCase}}',
                '{{modelNamePluralLowerCase}}',
                '{{baseUrl}}',
            ],
            [
                $name,
                Str::plural($name),
                $lowerName = strtolower($name),
                Str::plural(strtolower($name)),
                url('')
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

    public function generateCreate(string $name, array $columns)
    {
        $columnsToShowOnTable = [];
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
                $columnsToShowOnTable[implode(' ', $nameParts)] = $data->name;
            }
        }

        //Típus vizsgálat
        //ha text -> textarea
        //ha enum -> select
        //minden másra meg input


        $createTemplate = str_replace(
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
            $this->repgeneratorStubService->getStub('Frontend/Vue/create')
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
}
