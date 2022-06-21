<?php

namespace Pentacom\Repgenerator\Domain\Migration\ColumnGenerators;

use Pentacom\Repgenerator\Domain\Migration\Blueprint\Method;

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
