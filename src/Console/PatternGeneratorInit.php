<?php

namespace Pentacom\Repgenerator\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Pentacom\Repgenerator\Domain\Pattern\Services\RepgeneratorService;

/**
 * Class PatternGenerator.
 */
class PatternGeneratorInit extends Command
{
    /**
     * @var string
     */
    protected $signature = 'pattern:init {url : Your base url}'
    ;

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
            file_get_contents(__DIR__. '/../../frontend/.env')
        );

        file_put_contents(__DIR__. '/../../frontend/.env', $envFile);

        //Megnyitni a package.json-t és lecserélni benne az EXAMPLE_URL-t
        $packageJsonFile = str_replace(
            [
                'EXAMPLE_URL'
            ],
            [$baseUrl],
            file_get_contents(__DIR__. '/../../frontend/package.json')
        );

        file_put_contents(__DIR__. '/../../frontend/package.json', $packageJsonFile);

        //Futtatni egy npm instalt a frontend mappába
        $path = __DIR__. '/../../frontend/';
        exec("cd $path; npm instal; npm run build");
    }
}
