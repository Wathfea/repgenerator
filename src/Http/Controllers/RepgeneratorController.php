<?php

namespace Pentacom\Repgenerator\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\DBAL\Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Pentacom\Repgenerator\Domain\Pattern\Adapters\RepgeneratorColumnAdapter;
use Pentacom\Repgenerator\Http\Requests\GenerationRequest;
use Pentacom\Repgenerator\Domain\Migration\Blueprint\Table;
use Pentacom\Repgenerator\Domain\Migration\MigrationGeneratorService;
use Illuminate\Http\JsonResponse;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorService;

/**
 * Class RepgeneratorController
 */
class RepgeneratorController extends Controller
{
    const CRUD_MENU_TABLE_NAME = 'crud_menu';
    const CRUD_MENU_NAME = 'CrudMenu';

    /**
     * @param MigrationGeneratorService $migrationGeneratorService
     * @param RepgeneratorService $repgeneratorService
     */
    public function __construct(private MigrationGeneratorService $migrationGeneratorService, private RepgeneratorService $repgeneratorService)
    {
    }

    /**
     * @param  GenerationRequest  $request
     * @return JsonResponse
     * @throws Exception
     */
    public function generate(GenerationRequest $request): JsonResponse {
        $messages = [];
        $columns = [];
        $foreigns = [];

        foreach ( $request->get('columns') as $data ) {
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
                explode(',', $data['values']),
                $data['default'],
                $data['index'], //Ezzel chainelt index jön létre, nem alkalmas composite felvételre később ezt ha bekerül a composite külön kell kezelni majd
                $data['show_on_table'],
                $data['reference'],
                $data['foreign'],
            );

            $columnIndex = [];
            if($data['index'] != null) {
                foreach ($data['index'] as $cIndex) {
                    $columnIndex['type'] = $cIndex;
                }
            }

            if($data['foreign']) {
                $foreigns[] = [
                    'column' => $data['name'],
                    'reference' => $data['reference'],
                    'on' => $data['foreign'],
                    'onUpdate' => $data['cascade'] ? 'cascade' : null,
                    'onDelete' => $data['cascade'] ? 'cascade' : null,
                ];
            }
        }

        /* Migration Creation */
        $table = app(Table::class);
        $this->migrationGeneratorService->setup(config('pentacom.migration_target_path'), Carbon::now());

        $indexes = []; //Itt kell átadni majd ha composite akarunk készíteni külön sorban nem chainelve.

        //Detect if CrudMenus exists or we need to create it
        $messages = $this->shouldCreateCrudMenuTable($table);

        $table->setName($request->get('name'));
        $migrationName = $this->migrationGeneratorService->generateMigrationFiles($table, $columns, $indexes, $foreigns, $request->get('name'));
        $this->repgeneratorService->generate(
            $request->get('name'),
            $request->get('model', false),
            $request->get('pivot', false),
            $request->get('read_only', false),
            $migrationName,
            $columns,
            $foreigns,
            function($msg) use (&$messages) {
                $messages[] = $msg;
            });

        return response()->json(array_filter($messages, fn($value) => !is_null($value) && $value !== ''));
    }



    /**
     * @throws Exception
     */
    public function getTables(): JsonResponse
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        foreach ( $tables as $index => $tableName ) {
            $columnNames = array_keys(DB::connection()->getDoctrineSchemaManager()->listTableColumns($tableName));
            $columnData = [];
            foreach ( $columnNames as $columnName ) {
                $columnData[$columnName] = DB::connection()->getDoctrineColumn($tableName,$columnName)->getType()->getName();
            }
            $tables[$index] = [
                'name' => $tableName,
                'columns' => $columnData,
            ];
        }
        return response()->json($tables);
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
                $migrationName,
                $columns,
                [],
                function ($msg) use (&$messages) {
                    $messages[] = null;
                });
        }

        return $messages;
    }
}
