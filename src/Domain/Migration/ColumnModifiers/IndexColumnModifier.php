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
            foreach ($column['index'] as $type) {
                $method->chain($type);
            }
        }

        return $method;
    }
}
