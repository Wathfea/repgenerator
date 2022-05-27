<?php

namespace Pentacom\Repgenerator\MigrationGenerator\ColumnModifiers;

use Pentacom\Repgenerator\MigrationGenerator\Blueprint\Method;

/**
 * Class UnsignedColumnModifier
 */
class UnsignedColumnModifier
{
    /**
     * Set unsigned.
     *
     */
    public function chainUnsigned(Method $method, string $type, array $column): Method
    {
        if ($column['unsigned']) {
            if ($this->shouldAddUnsigned($type)) {
                $method->chain('unsigned');
            }

            return $method;
        }



        return $method;
    }

    /**
     * Check if column should add unsigned, by check the $type.
     * `integer`
     *
     * @param  string  $type
     * @return bool
     */
    private function shouldAddUnsigned(string $type): bool
    {
        return in_array($type, ['bigIncrements', 'bigInteger', 'id', 'increments', 'integer', 'smallIncrements', 'smallInteger', 'tinyIncrements', 'tinyInteger']);
    }

}
