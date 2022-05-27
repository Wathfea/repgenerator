<?php

namespace Pentacom\Repgenerator\MigrationGenerator\ColumnGenerators;


use Illuminate\Database\Schema\Builder;
use Pentacom\Repgenerator\MigrationGenerator\Blueprint\Method;

/**
 * Class StringColumnGenerator
 */
class StringColumnGenerator
{
    public const REMEMBER_TOKEN_LENGTH = 100;

    /**
     * @param  string  $type
     * @param  array  $column
     * @return Method
     */
    public function generate(string $type, array $column): Method
    {
        if ($column['name'] === 'remember_token') {
            return new Method('remember_token');
        }

        if ($column['length'] !== null && $column['length'] !== Builder::$defaultStringLength) {
            return new Method($type, $column['name'], $column['length']);
        } else {
            return new Method($type, $column['name']);
        }
    }
}
