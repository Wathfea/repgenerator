<?php

namespace Pentacom\Repgenerator\Domain\Migration\ColumnModifiers;

use Pentacom\Repgenerator\Domain\Migration\Blueprint\Method;

/**
 * Class IndexColumnModifier
 */
class IndexColumnModifier
{
    /**
     * Set simple index.
     *
     */
    public function chainIndex(Method $method, array $column): Method
    {
        if (key_exists('index', $column) && $column['index'] != null) {
            $method->chain($column['index']['type']);
        }

        return $method;
    }
}
