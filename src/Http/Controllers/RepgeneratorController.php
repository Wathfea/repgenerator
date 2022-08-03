<?php

namespace Pentacom\Repgenerator\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\DBAL\Exception;
use FilesystemIterator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Pentacom\Repgenerator\Domain\Migration\Blueprint\Table;
use Pentacom\Repgenerator\Domain\Migration\MigrationGeneratorService;
use Pentacom\Repgenerator\Domain\Pattern\Adapters\RepgeneratorColumnAdapter;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorService;
use Pentacom\Repgenerator\Http\Requests\GenerationFromTableRequest;
use Pentacom\Repgenerator\Http\Requests\GenerationRequest;
use Pentacom\Repgenerator\Models\RepgeneratorDomain;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

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
     * @param  GenerationRequest $request
     * @param  bool  $regenerate
     * @param  array  $regenerateData
     * @return JsonResponse
     * @throws Exception
     */
    public function generate(GenerationRequest $request, bool $regenerate = false, array $regenerateData = []): JsonResponse
    {
        $messages = [];
        $regenerate ? $requestData = $regenerateData : $requestData = $request->all();

        if(!RepgeneratorDomain::where('model', $requestData['name'])->count()) {
            $repgeneratorDomain = new RepgeneratorDomain();
            $repgeneratorDomain->model = $requestData['name'];
            $repgeneratorDomain->meta = json_encode($requestData);
            $repgeneratorDomain->save();
        }

        //Setup fields for generation and migration
        list($columns, $foreigns, $fileUploadFieldsData) = $this->fieldsSetup($requestData);

        $indexes = []; //Itt kell átadni majd ha composite akarunk készíteni külön sorban nem chainelve.

        //Migration generation setup
        /** @var Table $table */
        $table = app(Table::class);
        $this->migrationGeneratorService->setup(config('pentacom.migration_target_path'));

        //Only check  if not regenerate
        if(!$regenerate) {
            //Detect if CrudMenus exists or we need to create it
            $messages[] = $this->shouldCreateCrudMenuTable($table);
            sleep(1);
        }

        //Generate migration for the main model
        $messages[] = $this->generateMainMigrationAndDomain($table, $requestData, $columns, $indexes, $foreigns, $messages,
            $fileUploadFieldsData, $regenerate);
        sleep(1);

        //If $$fileUploadFieldsData is not empty we need to create the migration and the Domain for the relationship also
        if (!empty($fileUploadFieldsData)) {
            $messages[] = $this->generateFileRelationMigrationAndDomain($table, $requestData, $fileUploadFieldsData, $regenerate);
        }

        $messages = collect($messages)->flatten()->toArray();
        return response()->json(array_filter($messages, fn($value) => !is_null($value) && $value !== ''));
    }

    /**
     * @param  array  $requestData
     * @return array
     */
    private function fieldsSetup(array $requestData): array
    {
        $columns = [];
        $foreigns = [];
        $fileUploadFieldsData = [];

        foreach ($requestData['columns'] as $data) {
            if ($data['uploads_files_path'] != '') {
                $fileUploadFieldsData[] = [
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
                $data['is_file'] ?: false,
                $data['is_picture'] ?: false,
                $data['searchable'] ?: false,
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
        return array($columns, $foreigns, $fileUploadFieldsData);
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
                'url' => 'string',
                'icon' => 'string',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
            ];

            foreach ($migrationColumns as $name => $type) {
                $columns[] = new RepgeneratorColumnAdapter($name, $type);
            }

            $this->migrationGeneratorService->setDate(Carbon::now());
            $migrationName = $this->migrationGeneratorService->generateMigrationFiles($table, $columns, [], [],
                self::CRUD_MENU_NAME, 'menu');

            $this->repgeneratorService->generate(
                self::CRUD_MENU_NAME,
                true,
                false,
                false,
                false,
                $columns,
                [],
                function ($msg) use (&$messages) {
                    $messages[] = null;
                },
                false,
                null,
                $migrationName,
                false
            );
        }

        return $messages;
    }

    /**
     * @param  Table  $table
     * @param  array  $requestData
     * @param  mixed  $columns
     * @param  array  $indexes
     * @param  mixed  $foreigns
     * @param  array  $messages
     * @param  array  $fileUploadFieldsData
     * @param  bool  $regenerate
     * @return array
     */
    private function generateMainMigrationAndDomain(
        Table $table,
        array $requestData,
        mixed $columns,
        array $indexes,
        mixed $foreigns,
        array $messages,
        array $fileUploadFieldsData,
        bool $regenerate = false
    ): array {
        $table->setName($requestData['name']);

        //Only create migration if not regenerate
        $migrationName = null;
        if(!$regenerate) {
            $this->migrationGeneratorService->setDate(Carbon::now());
            $migrationName = $this->migrationGeneratorService->generateMigrationFiles(
                $table,
                $columns,
                $indexes,
                $foreigns,
                $requestData['name'],
                $requestData['icon']
            );
        }


        if (!empty($fileUploadFieldsData)) {
            $originalTable = $table->getName();
            $originalTableSingular = Str::singular($originalTable);

            $foreigns[] = [
                'relation_type' => 'HasMany',
                'related_model' => $requestData['name'].'File',
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
            $this->getTransformedName($requestData['name']),
            $requestData['model'] ? $requestData['name'] : false,
            $requestData['pivot'] ? $requestData['pivot'] :  false,
            true,
            $requestData['read_only'] ? $requestData['read_only'] :  false,
            $columns,
            $foreigns,
            function ($msg) use (&$messages) {
                $messages[] = $msg;
            },
            false,
            $fileUploadFieldsData,
            $migrationName,
            false
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
     * @param  array  $requestData
     * @param  array  $fileUploadFieldsData
     * @param  bool  $regenerate
     * @return array
     */
    private function generateFileRelationMigrationAndDomain(
        Table $table,
        array $requestData,
        array $fileUploadFieldsData,
        bool $regenerate = false
    ): array {
        $originalTable = $table->getName();
        $originalTableSingular = Str::singular($originalTable);
        $table->setName($requestData['name'].'_files');
        $columns = [];

        $migrationColumns = [
            'id' => 'id',
            $originalTableSingular.'_id' => 'unsignedBigInteger',
            'name' => 'string',
            'field' => 'string',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];

        foreach ($migrationColumns as $name => $type) {
            $columns[] = new RepgeneratorColumnAdapter($name, $type);
        }

        $foreigns[] = [
            'relation_type' => 'BelongsTo',
            'related_model' => $requestData['name'],
            'column' => $originalTableSingular.'_id',
            'reference' => [
                'name' => $originalTable
            ],
            'on' => 'id',
            'onUpdate' => null,
            'onDelete' => null
        ];

        //Only create migration if not regenerate
        $migrationName = null;
        if(!$regenerate) {
            $this->migrationGeneratorService->setDate(Carbon::now());
            $migrationName = $this->migrationGeneratorService->generateMigrationFiles(
                $table,
                $columns,
                [],
                $foreigns,
                $requestData['name'].'Files',
                'photograph'
            );
        }


        $this->repgeneratorService->generate(
            $this->getTransformedName($requestData['name'].'Files'),
            true,
            false,
            false,
            false,
            $columns,
            $foreigns,
            function ($msg) use (&$messages) {
                $messages[] = $msg;
            },
            false,
            $fileUploadFieldsData,
            $migrationName,
            true
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


    /**
     * @return JsonResponse
     */
    public function getGeneratedDomains(): JsonResponse
    {
        $domains = RepgeneratorDomain::all();
        return response()->json($domains);
    }

    /**
     * @param  GenerationFromTableRequest  $request
     * @return void
     * @throws Exception
     */
    public function reGenerate(GenerationFromTableRequest $request): void
    {
        foreach ($request->get('domains') as $domainMeta) {
            $domainData = json_decode($domainMeta, true);
            //Delete frontend resources
            $dir = base_path().'/resources/js/'.$domainData['name'];

            if(is_dir( $dir )) {
                $it = new RecursiveDirectoryIterator($dir, FilesystemIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($it,
                    RecursiveIteratorIterator::CHILD_FIRST);
                foreach($files as $file) {
                    if ($file->isDir()){
                        rmdir($file->getRealPath());
                    } else {
                        unlink($file->getRealPath());
                    }
                }
                rmdir($dir);
            }

            $this->generate(new GenerationRequest(), true, $domainData);
        }
    }
}
