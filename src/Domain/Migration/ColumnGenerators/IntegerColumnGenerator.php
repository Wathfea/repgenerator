<?php

namespace Pentacom\Repgenerator\Domain\Migration\ColumnGenerators;

use Pentacom\Repgenerator\Domain\Migration\Blueprint\Method;

/**
 * Class IntegerColumnGenerator
 */
class IntegerColumnGenerator
{
    /**
     * @param  string  $type
     * @param  array  $column
     * @return Method
     */
    public function generate(string $type, array $column): Method
    {
        if ($type === 'integer' && $column['aic']) {
            return new Method('increments', $column['name']);
        } else {
            return new Method($type, $column['name']);
        }
    }
}
