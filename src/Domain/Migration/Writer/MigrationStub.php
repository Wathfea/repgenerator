<?php

namespace Pentacom\Repgenerator\Domain\Migration\Writer;

use Illuminate\Support\Facades\File;

/**
 * Class MigrationStub
 */
class MigrationStub
{
    /**
     * Get the migration stub file.
     *
     * @param  string  $stubPath
     * @return string File content.
     */
    public function getStub(string $stubPath): string
    {
        return File::get($stubPath);
    }

    /**
     * Populates the place-holders in the migration stub.
     *
     * @param  string  $stub  File content.
     * @param  string  $use
     * @param  string  $upContent  Content for migration `up`.
     * @param  string  $downContent  Content for migration `down`.
     * @param  string  $nameKey
     * @return string Migration content.
     */
    public function populateStub(string $stub, string $use, string $upContent, string $downContent, string $nameKey): string
    {
        $content = $stub;
        $replace = [
            '{{ use }}'   => $use,
            '{{ up }}'    => $upContent,
            '{{ down }}'  => $downContent,
            '{{ nameKey }}'  => $nameKey,
        ];
        return str_replace(array_keys($replace), $replace, $content);
    }
}
