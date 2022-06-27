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
                $data['values'],
                $data['default'],
                $data['index'], //Ezzel chainelt index jön létre, nem alkalmas composite felvételre később ezt ha bekerül a composite külön kell kezelni majd
                $data['show_on_table']
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
        $indexes = [];

        //Vizsgálni létezik-e a crud_menu tábla
        if(!DB::connection()->getDoctrineSchemaManager()->tablesExist(Str::plural(self::CRUD_MENU_TABLE_NAME))) {
            $table->setName(self::CRUD_MENU_TABLE_NAME);
            $columns = [];

            $migrationColumns = [
                'id' => 'id',
                'name_key' => 'string',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
            ];

            foreach ($migrationColumns as $name => $type) {
                $columns[] = new RepgeneratorColumnAdapter($name,$type);
            }

            $migrationName = $this->migrationGeneratorService->generateMigrationFiles($table, $columns, [], [], self::CRUD_MENU_NAME);

            $this->repgeneratorService->generate(
                self::CRUD_MENU_NAME,
                true,
                false,
                false,
                $migrationName,
                $columns,
                [],
                function($msg) use (&$messages) {
                    $messages[] = null;
                });
        }

        $table->setName($request->get('name'));
        $this->migrationGeneratorService->generateMigrationFiles($table, $columns, $indexes, $foreigns, $request->get('name'));
        $this->repgeneratorService->generate(
            $request->get('name'),
            $request->get('model', false),
            $request->get('pivot', false),
            $request->get('read_only', false),
            null,
            $columns,
            $foreigns,
            function($msg) use (&$messages) {
                $messages[] = $msg;
            });

        return response()->json($messages);
    }



    /**
     * @throws Exception
     */
    public function getTables(): JsonResponse
    {
        $tables = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        foreach ( $tables as $index => $tableName ) {
            $tables[$index] = [
                'name' => $tableName,
                'columns' => array_keys(DB::connection()->getDoctrineSchemaManager()->listTableColumns($tableName))
            ];
        }
        return response()->json($tables);
    }
}
