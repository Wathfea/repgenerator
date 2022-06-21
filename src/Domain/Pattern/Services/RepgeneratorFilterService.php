<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Services;


use Illuminate\Support\Str;
use JetBrains\PhpStorm\ArrayShape;
use Pentacom\Repgenerator\Domain\Pattern\Adapters\RepgeneratorColumnAdapter;
use Pentacom\Repgenerator\Domain\Pattern\Helpers\CharacterCounterStore;

/**
 * Class RepgeneratorFilterService
 */
class RepgeneratorFilterService
{
    public function __construct(protected RepgeneratorStubService $repgeneratorStubService) {

    }


    /**
     * @param string $name
     * @param array $columns
     * @param array $foreigns
     * @return array
     */
    #[ArrayShape(['name' => "string", 'location' => "string"])]
    public function generate(string $name, array $columns, array $foreigns): array
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
                        $stub = $this->repgeneratorStubService->getFilterStub('Relationship');
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
            $this->repgeneratorStubService->getStub('Filter')
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

        CharacterCounterStore::addFileCharacterCount($path);

        return [
            'name' => "{$name}Filter.php",
            'location' => $path
        ];
    }
}
