<?php

namespace Pentacom\Repgenerator\Http\Controllers;

use App\Http\Controllers\Controller;
use Doctrine\DBAL\Exception;
use FilesystemIterator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Pentacom\Repgenerator\Domain\Gradient\GradientService;
use Pentacom\Repgenerator\Domain\Migration\Blueprint\Table;
use Pentacom\Repgenerator\Domain\Migration\MigrationGeneratorService;
use Pentacom\Repgenerator\Domain\Pattern\Adapters\RepgeneratorColumnAdapter;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorService;
use Pentacom\Repgenerator\Http\Requests\GenerationFromTableRequest;
use Pentacom\Repgenerator\Http\Requests\GenerationRequest;
use Pentacom\Repgenerator\Http\Requests\GradientRequest;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

/**
 * Class RepgeneratorController
 */
class RepgeneratorController extends Controller
{
    const CRUD_MENU_TABLE_NAME = 'crud_menu';
    const CRUD_MENU_GROUP_TABLE_NAME = 'crud_menu_group';

    /**
     * @param  MigrationGeneratorService  $migrationGeneratorService
     * @param  RepgeneratorService  $repgeneratorService
     * @param  GradientService  $gradientService
     */
    public function __construct(
        private MigrationGeneratorService $migrationGeneratorService,
        private RepgeneratorService $repgeneratorService,
        private GradientService $gradientService
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
            $messages[] = $this->shouldCreateCrudMenuGroupTable($table, $request);
            $messages[] = $this->shouldCreateCrudMenuTable($table, $request);
            sleep(1);
        }

        //Generate migration for the main model
        $messages[] = $this->generateMainMigrationAndDomain(
            table: $table,
            requestData: $requestData,
            columns: $columns,
            indexes: $indexes,
            foreigns: $foreigns,
            messages: $messages,
            fileUploadFieldsData: $fileUploadFieldsData,
            regenerate: $regenerate);
        sleep(1);

        //If $fileUploadFieldsData is not empty we need to create the migration and the Domain for the relationship also
        if (!empty($fileUploadFieldsData)) {
            $messages[] = $this->generateFileRelationMigrationAndDomain(
                table: $table,
                requestData: $requestData,
                fileUploadFieldsData: $fileUploadFieldsData,
                regenerate: $regenerate
            );
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
                $data['is_hashed'] ?: false,
                $data['is_crypted'] ?: false,
            );

            $columnIndex = [];
            if ($data['index'] != null) {
                foreach ($data['index'] as $cIndex) {
                    $columnIndex['type'] = $cIndex;
                }
            }

            if ($data['foreign']) {
                $foreigns[]  = [
                    'parentModel' => $requestData['name'],
                    'parentRelationType' => $data['foreignRelationType'],
                    'parentRelationName' => '', //Later generated from reference field ex user
                    'targetModel' => '', //Later generated from reference field
                    'targetRelationType' => $data['reference']['relationType'],
                    'targetRelationName' => '', //Later generated from parentModel field ex cats
                    'parentTableColumn' => $data['name'],
                    'referencedTable' => $data['reference']['name'],
                    'referencedTableColumn' => $data['foreign'],
                    'onUpdate' => $data['cascade'] ? 'cascade' : null,
                    'onDelete' => $data['cascade'] ? 'cascade' : null,
                ];
            }
        }
        return array($columns, $foreigns, $fileUploadFieldsData);
    }

    /**
     * @param  mixed  $table
     * @param  GenerationRequest  $request
     * @return array
     * @throws Exception
     */
    private function shouldCreateCrudMenuTable(mixed $table, GenerationRequest $request): array
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
                'crud_menu_group_id' => 'unsignedTinyInteger',
                'created_at' => 'timestamp',
                'updated_at' => 'timestamp',
            ];

            foreach ($migrationColumns as $name => $type) {
                if($name == 'crud_menu_group_id') {
                    $reference = [
                        'name' => 'crud_menu_group',
                        'relationType' => 'BelongsTo'
                    ];
                    $columns[] = new RepgeneratorColumnAdapter(
                        name: $name,
                        type: $type,
                        aic:false,
                        nullable: false,
                        cascade: false,
                        length: null,
                        comment: null,
                        precision: null,
                        scale: null,
                        unsigned: false,
                        values: null,
                        default: null,
                        index: null,
                        showOnTable: null,
                        references: $reference);
                } else {
                    $columns[] = new RepgeneratorColumnAdapter($name, $type);
                }

            }

            $foreigns[]  = [
                'parentModel' => 'Crud Menu',
                'parentRelationType' => 'BelongsTo',
                'parentRelationName' => '', //Later generated from reference field ex user
                'targetModel' => '', //Later generated from reference field
                'targetRelationType' => 'HasMany',
                'targetRelationName' => '', //Later generated from parentModel field ex cats
                'parentTableColumn' => 'id',
                'referencedTable' => 'crud_menu_groups',
                'referencedTableColumn' => 'crud_menu_group_id',
                'onUpdate' => null,
                'onDelete' => null,
            ];

            $this->migrationGeneratorService->setDate(Carbon::now());
            $migrationName = $this->migrationGeneratorService->generateMigrationFiles(
                table: $table,
                columns: $columns,
                indexes: [],
                foreigns: [],
                modelName: self::CRUD_MENU_TABLE_NAME,
                iconName: 'menu',
                isGenerateFrontend: false,
                softDelete: false,
                timestamps: false);

            $data = [
                'name' => self::CRUD_MENU_TABLE_NAME,
                'chosen_output_framework' => $request->chosen_output_framework,
                'icon' => $request->icon,
                'crudUrlPrefix' => '/',
            ];

            $this->repgeneratorService->generate(
                requestData: $data,
                columns: $columns,
                foreigns: $foreigns,
                callback: function ($msg) use (&$messages) {
                    $messages[] = null;
                },
                fileUploadFieldsData: null,
                migrationName: $migrationName,
                isGenerateFrontend: true
            );
        }

        return $messages;
    }

    /**
     * @param  mixed  $table
     * @param  GenerationRequest  $request
     * @return array
     * @throws Exception
     */
    private function shouldCreateCrudMenuGroupTable(mixed $table, GenerationRequest $request): array
    {
        $messages = [];
        if (!DB::connection()->getDoctrineSchemaManager()->tablesExist(Str::plural(self::CRUD_MENU_GROUP_TABLE_NAME))) {
            $table->setName(self::CRUD_MENU_GROUP_TABLE_NAME);
            $columns = [];

            $migrationColumns = [
                'id' => 'id',
                'name' => 'string',
                'icon' => 'string',
                'order' => 'integer'
            ];

            foreach ($migrationColumns as $name => $type) {
                $columns[] = new RepgeneratorColumnAdapter($name, $type);
            }

            $this->migrationGeneratorService->setDate(Carbon::now());
            $migrationName = $this->migrationGeneratorService->generateMigrationFiles(
                table: $table,
                columns: $columns,
                indexes: [],
                foreigns: [],
                modelName: self::CRUD_MENU_GROUP_TABLE_NAME,
                iconName: 'MenuIcon',
                isGenerateFrontend: false,
                softDelete: false,
                timestamps: false);

            $data = [
                'name' => self::CRUD_MENU_GROUP_TABLE_NAME,
                'chosen_output_framework' => $request->chosen_output_framework,
                'icon' => $request->icon,
                'crudUrlPrefix' => '/',
            ];


            $this->repgeneratorService->generate(
                requestData: $data,
                columns: $columns,
                foreigns: [],
                callback: function ($msg) use (&$messages) {
                    $messages[] = null;
                },
                fileUploadFieldsData: null,
                migrationName: $migrationName,
                isGenerateFrontend: true
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
                table: $table,
                columns: $columns,
                indexes: $indexes,
                foreigns: $foreigns,
                modelName: $requestData['name'],
                iconName: $requestData['icon'],
                isGenerateFrontend: $requestData['generateFrontend'],
                softDelete: $requestData['softDelete'],
                timestamps: $requestData['timestamps'],
                menuGroupId: $requestData['menu_group_id'],
                newMenuGroupName: $requestData['new_menu_group_name'],
                newMenuGroupIcon: $requestData['new_menu_group_icon'],
                crudUrlPrefix: $requestData['crudUrlPrefix']
            );
        }

//        if (!empty($fileUploadFieldsData)) {
//            $originalTable = $table->getName();
//            $originalTableSingular = Str::singular($originalTable);
//
//            $foreigns[]  = [
//                'parentModel' => $requestData['name'],
//                'parentRelationType' => 'HasMany',
//                'parentRelationName' => 'files', //Later generated from reference field ex user
//                'targetModel' => $requestData['name'].'File', //Later generated from reference field
//                'targetRelationType' => 'BelongsTo',
//                'targetRelationName' => '', //Later generated from parentModel field ex cats
//                'parentTableColumn' => $originalTableSingular.'_id',
//                'referencedTable' => $originalTable,
//                'referencedTableColumn' => 'id',
//                'onUpdate' => null,
//                'onDelete' => null,
//            ];
//
//        }

        $this->repgeneratorService->generate(
            requestData: $requestData,
            columns: $columns,
            foreigns: $foreigns,
            callback: function ($msg) use (&$messages) {
                $messages[] = $msg;
            },
            fileUploadFieldsData: $fileUploadFieldsData,
            migrationName: $migrationName,
            isGenerateFrontend: $requestData['generateFrontend']
        );
        return $messages;
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
            'ext' => 'string',
            'mime' => 'string',
            'created_at' => 'timestamp',
            'updated_at' => 'timestamp',
        ];

        foreach ($migrationColumns as $name => $type) {
            $columns[] = new RepgeneratorColumnAdapter($name, $type);
        }

        $foreigns[]  = [
            'parentModel' => $requestData['name'].'File',
            'parentRelationType' => 'BelongsTo',
            'parentRelationName' => '', //Later generated from reference field ex user
            'targetModel' => '', //Later generated from reference field
            'targetRelationType' => 'HasMany',
            'targetRelationName' => '', //Later generated from parentModel field ex cats
            'parentTableColumn' => $originalTableSingular.'_id',
            'referencedTable' => $originalTable,
            'referencedTableColumn' => 'id',
            'onUpdate' => null,
            'onDelete' => null,
        ];

        //Only create migration if not regenerate
        $migrationName = null;
        if(!$regenerate) {
            $this->migrationGeneratorService->setDate(Carbon::now());
            $migrationName = $this->migrationGeneratorService->generateMigrationFiles(
                table: $table,
                columns: $columns,
                indexes: [],
                foreigns: $foreigns,
                modelName: $requestData['name'].'Files',
                iconName: 'photograph',
                isGenerateFrontend: false,
                softDelete: false,
                timestamps: false
            );
        }

        $data = [
            'name' => $requestData['name'].'Files',
            'chosen_output_framework' => $requestData['chosen_output_framework']
        ];

        $this->repgeneratorService->generate(
            requestData: $data,
            columns: $columns,
            foreigns: $foreigns,
            callback: function ($msg) use (&$messages) {
                $messages[] = $msg;
            },
            fileUploadFieldsData: $fileUploadFieldsData,
            migrationName: $migrationName,
            isGenerateFrontend: false,
            isGeneratedFileDomain: true
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
                'relationType' => null,
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
        $domains = [];
        $directories = array_filter(glob(app_path('Domain').'/*'), 'is_dir');

        foreach ($directories as $directory) {
            if(file_exists($directory.'/config.php')){
                $config = include($directory.'/config.php');
                $domains[] = [
                    'model' => $config['name'],
                    'meta' => $config['meta'],
                ];
            }
        }

        return response()->json($domains);
    }

    /**
     * @param  string  $table
     * @return JsonResponse
     */
    public function validateTable(string $table): JsonResponse
    {
        $isSingular = Str::singular($table) === $table;
        $isExists = Schema::hasTable(strtolower(str_replace(' ', '_', Str::plural($table))));

        $valid = false;
        if($isSingular && !$isExists) {
            $valid = true;
        }
        return response()->json($valid);
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
            $dir = base_path().DIRECTORY_SEPARATOR."resources".DIRECTORY_SEPARATOR."js".DIRECTORY_SEPARATOR."Domain".DIRECTORY_SEPARATOR.$domainData['name'];

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

            $this->generate(request: new GenerationRequest(), regenerate: true, regenerateData: $domainData);
        }
    }

    /**
     * @param  GradientRequest  $request
     * @return JsonResponse
     */
    public function generateGradient(GradientRequest $request): JsonResponse
    {
        $colors = $this->gradientService->generateGradients(
            theColorBegin: $request->get('hexFrom'),
            theColorEnd: $request->get('hexTo'),
            theNumSteps: 8);
        return response()->json($colors);
    }

    public function checkFrontendVersion()
    {
        $upgradeFrontend = false;

        if (file_exists($path = base_path('.repgenerator'))) {
            $localVersion = (float) file_get_contents($path);

            $versionFile = include_once (__DIR__.'/../../../config/frontend.php');
            $frontendVersion = (float) $versionFile['frontend_version'];


            if($localVersion < $frontendVersion) {
                $upgradeFrontend = true;
            }
        }

        return response()->json($upgradeFrontend);

    }
}
