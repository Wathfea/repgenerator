<?php

namespace Pentacom\Repgenerator\Console;

use Illuminate\Console\Command;

class MigrationGenerator extends Command
{
    /**
     * @var string
     */
    protected $signature = 'migration:generate {name : Migration name}';

    /**
     * @var string
     */
    protected $description = 'Generate a Laravel migration file';

    /**
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return void
     */
    public function handle()
    {
        $name = $this->argument('name');
        $this->info('Generating migration');
        $this->call('make:migration', ['name' => 'create_'.$name.'_table']);
        $this->info('Blank migration is ready!');
    }
}
