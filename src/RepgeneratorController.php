<?php

namespace Pentacom\Repgenerator;

use App\Http\Controllers\Controller;
use Doctrine\DBAL\Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Pentacom\Repgenerator\Console\MigrationGenerator;
use Pentacom\Repgenerator\MigrationGenerator\Blueprint\Method;
use Pentacom\Repgenerator\MigrationGenerator\Blueprint\Table;
use Pentacom\Repgenerator\MigrationGenerator\MigrationGeneratorService;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Class RepgeneratorController
 */
class RepgeneratorController extends Controller
{
    /**
     * @param  MigrationGeneratorService  $migrationGeneratorService
     */
    public function __construct(public MigrationGeneratorService $migrationGeneratorService)
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
     * @param  Request  $request
     * @return Application|Factory|View
     */
    public function wizzardInstall(Request $request) {
        /** @var RepgeneratorService $service */
        $service = app(RepgeneratorService::class);
        $messages = [];
        $columns = [];
        $foreigns = [];

        if ( $request->has('columnNames') ) {

            $columnTypes = $request->get('columnTypes');
            $columnAutoIncrements = $request->get('columnAutoIncrements', []);
            $columnNullables = $request->get('columnNulls', []);
            $columnCascades = $request->get('columnCascades',[]);
            $columnLengths = $request->get('columnLengths', []);
            $columnComments = $request->get('columnComments', []);
            $columnPrecisions = $request->get('columnPrecisions', []);
            $columnScales = $request->get('columnScales',[]);
            $columnUnsigneds = $request->get('columnUnsigneds',[]);
            $columnValues = $request->get('columnValues',[]);
            $columnDefaults = $request->get('columnDefaults', []);
            $columnIndexes = $request->get('columnIndexes', []);
            $columnReferences = $request->get('columnReferences', []);
            $columnForeigns = $request->get('columnForeigns', []);

            //TODO
            $columnSearchables = $request->get('columnSearchables', []);

            foreach ( $request->get('columnNames') as $index => $columName ) {
                $aic = key_exists($index,$columnAutoIncrements) ? $columnAutoIncrements[$index] : false;
                $nullable = key_exists($index,$columnNullables) ? $columnNullables[$index] : false;
                $cascades = key_exists($index,$columnCascades) ? $columnCascades[$index] : false;
                $length = key_exists($index,$columnLengths) ? $columnLengths[$index] : false;
                $comment = key_exists($index,$columnComments) ? $columnComments[$index] : '';
                $precision = key_exists($index,$columnPrecisions) ? $columnPrecisions[$index] : 0;
                $scale = key_exists($index,$columnScales) ? $columnScales[$index] : 0;
                $unsigned = key_exists($index,$columnUnsigneds) ? $columnUnsigneds[$index] : false;
                $value = key_exists($index,$columnValues) ? $columnValues[$index] : null;
                $default = key_exists($index,$columnDefaults) ? $columnDefaults[$index] : null;
                $indexes = key_exists($index,$columnIndexes) ? $columnIndexes[$index] : null;
                $foreign = key_exists($index,$columnForeigns) ? $columnForeigns[$index] : null;
                $reference = key_exists($index,$columnReferences) ? $columnReferences[$index] : null;

                $columnIndex = [];
                if($indexes != null) {
                    foreach ($indexes as $cIndex) {
                        $columnIndex['type'] = $cIndex;
                    }
                }

                if($foreign) {
                    $foreigns[] = [
                        'column' => $columName,
                        'reference' => $reference,
                        'on' => $foreign,
                        'onUpdate' => $cascades ? 'cascade' : null,
                        'onDelete' => $cascades ? 'cascade' : null,
                    ];
                }

                $columns[] = new RepgeneratorColumnAdapter(
                    $columName,
                    $columnTypes[$index],
                    $aic,
                    $nullable,
                    $cascades,
                    $length,
                    $comment,
                    $precision,
                    $scale,
                    $unsigned,
                    $value,
                    $default,
                    $columnIndex //Ezzel chainelt index jön létre, nem alkalmas composite felvételre később ezt ha bekerül a composite külön kell kezelni majd
                );
            }
        }

        /* Migration Creation */
        $table = app(Table::class);
        $table->setName($request->get('name'));

        $this->migrationGeneratorService->setup(config('pentacom.migration_target_path'), Carbon::now());
        //Itt az indexes lenne használható composite index létrehozásra de az induláshoz ezt még nem rakjuk bele
        // Formátum
        //        $indexes = [
        //            [
        //                'type' => 'unique',
        //                'columns' => ['email','teszt'] //Composite index
        //            ],
        //            [
        //                'type' => 'primary',
        //                'columns' => ['primary_teszt'] //Normál index
        //            ],
        //        ];
        $indexes = [];
        $this->migrationGeneratorService->generateMigrationFiles($table, $columns, $indexes, $foreigns);

        $service->generate(
            $request->get('name'),
            $request->session()->has('model'),
            $request->session()->has('pivot'),
            $request->session()->has('readonly'),
            $columns,
            $foreigns,
            function($msg) use (&$messages) {
                $messages[] = $msg;
            });

        return view('repgenerator-wizzard::finish', [
            'messages' => $messages
        ]);
    }

    /**
     * @param  int  $currentStep
     * @return array
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function getSteps(int $currentStep = 1): array
    {
        $createRepositoryTitle = 'Create repository';
        if ( request()->has('name') ) {
            $createRepositoryTitle .= ': ' . request()->get('name');
        }
        $stepTitles = [
          $createRepositoryTitle, 'Create migration', 'Overview'
        ];
        $steps = [];
        foreach ( $stepTitles as $index => $stepTitle ) {
            $stepNumber = $index+1;
            $steps[] = [
                'title' => $stepTitle,
                'number' => $stepNumber,
                'index' => $index > 9 ? $stepNumber : '0' . $stepNumber,
                'complete' => $stepNumber < $currentStep,
                'current' => $stepNumber == $currentStep
            ];
        }
        return $steps;
    }


    /**
     * @param  Request  $request
     * @param  int  $stepNumber
     * @return Application|Factory|View
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws Exception
     */
    public function wizzardStep(Request  $request, int $stepNumber): View|Factory|Application
    {

        $data = [];
        $options = [];

        if ( $stepNumber == 1) {
            $options = [
                'Model' => 'Generate Eloquent Model',
                'Pivot' => 'Is this a pivot model?',
                'Readonly' => 'Is the repository readonly?'
            ];
        } else if ( $stepNumber == 2 ) {
            $options = [
                'Seed' => 'Generate a Seeder // TODO!',
            ];

            $request->session()->put('name', $request->get('name'));
            $request->session()->put('model', $request->get('model'));
            $request->session()->put('pivot', $request->get('pivot'));
            $request->session()->put('readonly', $request->get('readonly'));
        }

        $data['columns'] = MigrationGeneratorService::getDefaultColumnSettings();
        $data['columnTypes'] = MigrationGeneratorService::getColumnTypes();


        $columnFields = [
            'type' => 'columnTypes',
            'length' => 'columnLengths',
            'precision' => 'columnPrecisions',
            'scale' => 'columnScales',
            'default' => 'columnDefaults',
            'auto_increment' => 'columnAutoIncrements',
            'nullable' => 'columnNulls',
            'reference' => 'columnReferences',
            'foreign' => 'columnForeigns',
            'cascade' => 'columnCascades',
            'searchable' => 'columnSearchables',
            'values' => 'columnValues',
            'comment' => 'columnComments',
            'unsigned' => 'columnUnsigneds',
            'index' => 'columnIndexes',
        ];

        if ( $request->has('columnNames') ) {
            foreach ( $request->get('columnNames') as $index => $columnName ) {
                $data['columns'][$columnName] = [];
                foreach ( $columnFields as $columnFieldKey => $columnFieldName ) {
                    if ( $request->has($columnFieldName) && key_exists($index, $request->get($columnFieldName)) ) {
                        if($request->get($columnFieldName)[$index] == 'on') {
                            $data['columns'][$columnName][$columnFieldKey] = 1;
                        }
                        else {
                            $data['columns'][$columnName][$columnFieldKey] = $request->get($columnFieldName)[$index];
                        }
                    } else {
                        $data['columns'][$columnName][$columnFieldKey] = 0;
                    }
                }
            }
        }

        $data['models'] = DB::connection()->getDoctrineSchemaManager()->listTableNames();
        $data['options'] = $options;
        $data['step'] = $stepNumber;
        $data['query'] = key_exists('QUERY_STRING',$_SERVER) ? '?' . $_SERVER['QUERY_STRING'] : '';
        $data['steps'] = $this->getSteps($stepNumber);

        $isFinalStep = count($data['steps']) == $stepNumber;

        $data['route'] = $isFinalStep ? route('repwizz.finish') :  route('repwizz.step', ['step'=>$stepNumber+1]);
        $data['method'] = $isFinalStep ? 'post' :  'get';

        return view('repgenerator::index', $data);
    }


    /**
     * @return Application|RedirectResponse|Redirector
     */
    public function wizzard(): Redirector|RedirectResponse|Application
    {
        return redirect(route('repwizz.step', ['step'=>1]));
    }
}
