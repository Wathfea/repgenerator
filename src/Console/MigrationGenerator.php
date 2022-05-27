<?php

namespace Pentacom\Repgenerator\Console;

use Illuminate\Console\Command;

class MigrationGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration:generate {name : Migration name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a Laravel migration file';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $name = $this->argument('name');

        $this->info('Generating migration');

        //Blank migration
        $this->call('make:migration', ['name' => 'create_'.$name.'_table']);
        $this->info('Blank migration is ready!');

    }
}
