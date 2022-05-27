<?php

namespace Pentacom\Repgenerator\MigrationGenerator\Writer;

use Illuminate\Support\Facades\File;
use Pentacom\Repgenerator\MigrationGenerator\Blueprint\ToStringInterface;

/**
 * Class MigrationWriter
 */
class MigrationWriter
{
    /**
     * @param  MigrationStub  $migrationStub
     */
    public function __construct(private MigrationStub $migrationStub)
    {
    }

    /**
     * Writes migration to destination.
     *
     * @param  string  $path  Migration file destination path.
     * @param  string  $stubPath  Migration stub file path.
     * @param  ToStringInterface $up  migration up method
     * @param  ToStringInterface $down  migration down method
     */
    public function writeTo(string $path, string $stubPath, ToStringInterface $up, ToStringInterface $down): void {
        $stub = $this->migrationStub->getStub($stubPath);


        $use = implode(Constants::LINE_BREAK, [
            'use Illuminate\Database\Migrations\Migration;',
            'use Illuminate\Database\Schema\Blueprint;',
            'use Illuminate\Support\Facades\Schema;',
        ]);

        dd($this->migrationStub->populateStub($stub, $use, $up->toString(), $down->toString()));

        File::put($path, $this->migrationStub->populateStub($stub, $use, $up->toString(), $down->toString()));

    }
}
