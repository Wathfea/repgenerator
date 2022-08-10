<?php

namespace Pentacom\Repgenerator\Console;

use Illuminate\Console\Command;

/**
 * Class PatternGenerator.
 */
class PatternGeneratorInit extends Command
{
    /**
     * @var string
     */
    protected $signature = 'pattern:init {url : Your base url}';

    /**
     * @var string
     */
    protected $description = 'Initialize Laravel Repository Pattern Generator';

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
        $baseUrl = $this->argument('url');

        $envFile = str_replace(
            [
                'EXAMPLE_URL'
            ],
            [$baseUrl],
            file_get_contents(base_path('vendor').'/pentacom/repgenerator/frontend/.env.example')
        );

        file_put_contents(base_path('vendor').'/pentacom/repgenerator/frontend/.env', $envFile);

        //Megnyitni a package.json-t és lecserélni benne az EXAMPLE_URL-t
        $packageJsonFile = str_replace(
            [
                'EXAMPLE_URL'
            ],
            [$baseUrl],
            file_get_contents(base_path('vendor').'/pentacom/repgenerator/frontend/package.json')
        );

        file_put_contents(base_path('vendor').'/pentacom/repgenerator/frontend/package.json', $packageJsonFile);

        //Futtatni egy npm instalt a frontend mappába
        $path = base_path('vendor').'/pentacom/repgenerator/frontend/';
        exec("cd $path && npm install && npm run build");
    }
}
