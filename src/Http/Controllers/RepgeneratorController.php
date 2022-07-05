<?php

namespace Pentacom\Repgenerator\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\DBAL\Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Pentacom\Repgenerator\Domain\Migration\Blueprint\Table;
use Pentacom\Repgenerator\Domain\Migration\MigrationGeneratorService;
use Pentacom\Repgenerator\Domain\Pattern\Adapters\RepgeneratorColumnAdapter;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorService;
use Pentacom\Repgenerator\Http\Requests\GenerationRequest;

/**
 * Class RepgeneratorController
 */
class RepgeneratorController extends Controller
{
    const CRUD_MENU_TABLE_NAME = 'crud_menu';
    const CRUD_MENU_NAME = 'CrudMenu';

    /**
     * @param  MigrationGeneratorService  $migrationGeneratorService
     * @param  RepgeneratorService  $repgeneratorService
     */
    public function __construct(
        private MigrationGeneratorService $migrationGeneratorService,
        private RepgeneratorService $repgeneratorService
    ) {
    }

    /**
     * @param  GenerationRequest  $request
     * @return JsonResponse
     * @throws Exception
     */
    public function generate(GenerationRequest $request): JsonResponse
    {
        //Setup fields for generation and migration
        list($columns, $foreigns, $fileUpload) = $this->fieldsSetup($request);
        $indexes = []; //Itt kell átadni majd ha composite akarunk készíteni külön sorban nem chainelve.

        //Migration generation setup
        /** @var Table $table */
        $table = app(Table::class);
        $this->migrationGeneratorService->setup(config('pentacom.migration_target_path'), Carbon::now());


        //Detect if CrudMenus exists or we need to create it
        $messages[] = $this->shouldCreateCrudMenuTable($table);
        sleep(1);

        //Generate migration for the main model
        $messages[] = $this->generateMainMigrationAndDomain($table, $request, $columns, $indexes, $foreigns, $messages,
            $fileUpload);
        sleep(1);

        //If $fileUpload is not empty we need to create the migration and the Domain for the relationship also
        if(!empty($fileUpload)) {
            $messages[] = $this->generateFileRelationMigration($table, $request);
        }

        $messages = collect($messages)->flatten()->toArray();
        return response()->json(array_filter($messages, fn($value) => !is_null($value) && $value !== ''));
    }

    /**
     * @param  GenerationRequest  $request
     * @return array
     */
    private function fieldsSetup(GenerationRequest $request): array
    {
        $columns = [];
        $foreigns = [];
        $fileUpload = [];

        foreach ($request->get('columns') as $data) {
            if ($data['uploads_files_path'] != '') {
                $fileUpload = [
                    'path' => $data['uploads_files_path'],
                    'field' => $data['name']
                ];
            }

            $columns[] = new RepgeneratorColumnAdapter(
                $data['name'],
                $data['type'],
                $data['auto_increment'],
                $data['nullable'],
                $data['cascade'] ?: false,
                $data['length'],
                $data['comment'],
                $data['precision'],
                $data['scale'],
                $data['unsigned'],
                empty($data['values']) ? null : explode(',', $data['values']),
                $data['default'],
                $data['index'],
                //Ezzel chainelt index jön létre, nem alkalmas composite felvételre később ezt ha bekerül a composite külön kell kezelni majd
                $data['show_on_table'],
                $data['reference'],
                $data['foreign'],
                $data['uploads_files_path'],
                false
            );

            $columnIndex = [];
            if ($data['index'] != null) {
                foreach ($data['index'] as $cIndex) {
                    $columnIndex['type'] = $cIndex;
                }
            }

            if ($data['foreign']) {
                $foreigns[] = [
                    'column' => $data['name'],
                    'reference' => $data['reference'],
                    'on' => $data['foreign'],
                    'onUpdate' => $data['cascade'] ? 'cascade' : null,
                    'onDelete' => $data['cascade'] ? 'cascade' : null,
                ];
            }
        }
        return array($columns, $foreigns, $fileUpload);
    }

    /**
     * @param  mixed  $table
     * @return array
     * @throws Exception
     */
    private function shouldCreateCrudMenuTable(mixed $table): array
    {
        $messages = [];
        if (!DB::connection()->getDoctrineSchemaManager()->tablesExist(Str::plural(self::CRUD_MENU_TABLE_NAME))) {
            $table->setName(self::CRUD_MENU_TABLE_NAME);
            $columns = [];

            $migrationColumns = [
                'id' => 'id',
                'name' => 'string',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
            ];

            foreach ($migrationColumns as $name => $type) {
                $columns[] = new RepgeneratorColumnAdapter($name, $type);
            }

            $migrationName = $this->migrationGeneratorService->generateMigrationFiles($table, $columns, [], [],
                self::CRUD_MENU_NAME);

            $this->repgeneratorService->generate(
                self::CRUD_MENU_NAME,
                true,
                false,
                false,
                $columns,
                [],
                function ($msg) use (&$messages) {
                    $messages[] = null;
                },
                false,
                null,
                $migrationName
            );
        }

        return $messages;
    }

    /**
     * @param  Table  $table
     * @param  GenerationRequest  $request
     * @param  mixed  $columns
     * @param  array  $indexes
     * @param  mixed  $foreigns
     * @param  array  $messages
     * @param  array  $fileUpload
     * @return array
     */
    private function generateMainMigrationAndDomain(
        Table $table,
        GenerationRequest $request,
        mixed $columns,
        array $indexes,
        mixed $foreigns,
        array $messages,
        array $fileUpload
    ): array {
        $table->setName($request->get('name'));

        $migrationName = $this->migrationGeneratorService->generateMigrationFiles(
            $table,
            $columns,
            $indexes,
            $foreigns,
            $request->get('name')
        );

        if(!empty($fileUpload)) {
            $originalTable = $table->getName();
            $originalTableSingular = Str::singular($originalTable);

            $foreigns[] = [
                'relation_type' => 'HasMany',
                'related_model' => $request->get('name').'File',
                'relation_name' => 'files',
                'column' => $originalTableSingular.'_id',
                'reference' => [
                    'name' => $originalTable
                ],
                'on' => 'id',
                'onUpdate' => null,
                'onDelete' => null
            ];
        }

        $this->repgeneratorService->generate(
            $this->getTransformedName($request->get('name')),
            $request->get('model', false),
            $request->get('pivot', false),
            $request->get('read_only', false),
            $columns,
            $foreigns,
            function ($msg) use (&$messages) {
                $messages[] = $msg;
            },
            false,
            $fileUpload,
            $migrationName,
        );
        return $messages;
    }

    /**
     * @param  string  $name
     * @return string|array
     */
    private function getTransformedName(string $name): string|array
    {
        return str_replace(' ', '', Str::singular($name));
    }

    /**
     * @param  Table  $table
     * @param  GenerationRequest  $request
     * @return array
     */
    private function generateFileRelationMigration(
        Table $table,
        GenerationRequest $request
    ): array {
        $originalTable = $table->getName();
        $originalTableSingular = Str::singular($originalTable);
        $table->setName($request->get('name').'_files');
        $columns = [];

        $migrationColumns = [
            'id' => 'id',
            $originalTableSingular.'_id' => 'unsignedBigInteger',
            'name' => 'string',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];

        foreach ($migrationColumns as $name => $type) {
            $columns[] = new RepgeneratorColumnAdapter($name, $type);
        }

        $foreigns[] = [
            'relation_type' => 'BelongsTo',
            'related_model' => $request->get('name'),
            'column' => $originalTableSingular.'_id',
            'reference' => [
                'name' => $originalTable
            ],
            'on' => 'id',
            'onUpdate' => null,
            'onDelete' => null
        ];

        $migrationName = $this->migrationGeneratorService->generateMigrationFiles(
            $table,
            $columns,
            [],
            $foreigns,
            $request->get('name').'Files'
        );

        $this->repgeneratorService->generate(
            $this->getTransformedName($request->get('name').'Files'),
            true,
            false,
            false,
            $columns,
            $foreigns,
            function ($msg) use (&$messages) {
                $messages[] = $msg;
            },
            false,
            null,
            $migrationName,
        );

        return $messages;
    }

    /**
     * @throws Exception
     */
    public function getTables(): JsonResponse
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        foreach ($tables as $index => $tableName) {
            $columnNames = array_keys(DB::connection()->getDoctrineSchemaManager()->listTableColumns($tableName));
            $columnData = [];
            foreach ($columnNames as $columnName) {
                $columnData[$columnName] = DB::connection()->getDoctrineColumn($tableName,
                    $columnName)->getType()->getName();
            }
            $tables[$index] = [
                'name' => $tableName,
                'columns' => $columnData,
            ];
        }
        return response()->json($tables);
    }
}
