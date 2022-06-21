<?php

namespace Pentacom\Repgenerator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorService;

/**
 * Class PatternGenerator.
 */
class PatternGenerator extends Command
{
    /**
     * @var string
     */
    protected $signature = 'pattern:generate
                        {name : Class (singular) for example User}
                        {--M|model : Whether the generator should generate a model}
                        {--m|migration : Whether the generator should generate a migration}
                        {--P|pivot : Whether the generator should generate a pivot repo or default to model}
                        {--R|readonly : Whether the generator should generate a readonly controller}'
    ;

    /**
     * @var string
     */
    protected $description = 'Generate Laravel Repository Pattern';

    /**
     * @var array
     */
    protected array $generatedFiles = [];

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Generate the pattern files.
     */
    public function handle()
    {
        $name = $this->argument('name');
        $generateModel = $this->option('model');
        $generateMigration = $this->option('migration');
        $generatePivot = $this->option('pivot');
        $readOnly = $this->option('readonly');

        if(Str::singular($name) !== $name) {
            $this->error('Name should be singular!');
            exit;
        }

        if (file_exists($path = app_path("Domain/{$name}"))) {
            $this->error('This repository already exists!');
            exit;
        }

        $this->info('Generating...');

        /** @var RepgeneratorService $service */
        $service = app(RepgeneratorService::class);
        $service->setCmd($this);

        $service->generate($name, $generateModel, $generatePivot, $readOnly, [], [], function($msg){
            $this->info($msg);
        }, true);


        if($generateMigration) {
            $passName = strtolower(Str::plural($name));
            $this->call('migration:generate', ['name' => $passName]);
        }
    }
}
