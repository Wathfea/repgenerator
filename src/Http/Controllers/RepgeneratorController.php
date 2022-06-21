<?php

namespace Pentacom\Repgenerator\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\DBAL\Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
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

    /**
     * @param MigrationGeneratorService $migrationGeneratorService
     * @param RepgeneratorService $repgeneratorService
     */
    public function __construct(private MigrationGeneratorService $migrationGeneratorService, private RepgeneratorService $repgeneratorService)
    {
    }

    /**
     * @param  Request  $request
     */
    public function migrationTesting(Request $request) {
        $columns = [
            [
                'name' => 'id',
                'type' => 'integer',
                'aic' => false,
                'nullable' => false,
                'cascade' => true, //Nincs kezelve
                'length' => null,
                'comment' => null,
                'precision' => 0,
                'scale' => 0,
                'unsigned' => false,
                'values' => null, // Enum esetén, tömb egyébként null
                'default' => null,
                'index' => [
                    'type' => 'index', //primary,unique,index,fulltext,spatialIndex
                ]
            ],
        ];

        $indexes = [
            [
                'type' => 'unique',
                'columns' => ['email','teszt'] //Composite index
            ],
            [
                'type' => 'primary',
                'columns' => ['primary_teszt'] //Normál index
            ],
        ];

        $foreigns = [
          [
              'column' => 'user_id',
              'reference' => 'id',
              'on' => 'users',
              'onUpdate' => null,
              'onDelete' => null
          ]
        ];

        /* Migration Creation */
        $table = app(Table::class);
        $table->setName($request->get('name'));


        //@TODO a path és migration date kivezetése a wizzardba mint állítható paraméter
        $this->migrationGeneratorService->setup($request->get('path'), $request->get('migration_date'));
        $this->migrationGeneratorService->generateMigrationFiles($table, $columns, $indexes, $foreigns);

    }

    /**
     * @param  GenerationRequest  $request
     * @return JsonResponse
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
        $table->setName($request->get('name'));

        $this->migrationGeneratorService->setup(config('pentacom.migration_target_path'), Carbon::now());
        $indexes = [];
        $this->migrationGeneratorService->generateMigrationFiles($table, $columns, $indexes, $foreigns);
        $this->repgeneratorService->generate(
            $request->get('name'),
            $request->get('model', false),
            $request->get('pivot', false),
            $request->get('read_only', false),
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
