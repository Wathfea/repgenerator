<?php

namespace Pentacom\Repgenerator\MigrationGenerator\ColumnGenerators;

use Pentacom\Repgenerator\MigrationGenerator\Blueprint\Method;

/**
 * Class DefaultColumnGenerator
 */
class DefaultColumnGenerator
{
    /**
     * @param  string  $type
     * @param  array  $column
     * @return Method
     */
    public function generate(string $type, array $column): Method
    {
        return new Method($type, $column['name']);
    }
}
