<?php

namespace Pentacom\Repgenerator\Domain\Migration;


use Pentacom\Repgenerator\Domain\Migration\Blueprint\Method;

/**
 * Class IndexGenerator
 */
class IndexGenerator
{
    /**
     * Converts index into migration method.
     *
     */
    public function generate(array $index): Method
    {
        if(count($index['columns']) === 1) {
            return new Method($index['type'], $index['columns'][0]);
        } else {
            return new Method($index['type'], $index['columns']);
        }
    }
}
