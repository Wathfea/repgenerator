<?php

namespace Pentacom\Repgenerator\Console;

use Illuminate\Console\Command;

class ClearFiles extends Command
{
    /**
     * @var string
     */
    protected $signature = 'pattern:clear';

    /**
     * @var string
     */
    protected $description = 'Delete Laravel Repository Pattern Generated stuff';

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
        $dirs = [
            app_path("Abstraction"),
            app_path("Domain"),
            resource_path("js".DIRECTORY_SEPARATOR."Abstraction"),
            resource_path("js".DIRECTORY_SEPARATOR."Domain"),
        ];

        foreach ($dirs as $dir) {
            $this->deleteDir($dir);
        }
    }

    private  function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }

}
