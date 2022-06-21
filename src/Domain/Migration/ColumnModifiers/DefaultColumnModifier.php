<?php

namespace Pentacom\Repgenerator\Domain\Migration\ColumnModifiers;

use Pentacom\Repgenerator\Domain\Migration\Blueprint\Method;

/**
 * Class DefaultColumnModifier
 */
class DefaultColumnModifier
{
    /**
     * Set default value.
     *
     */
    public function chainDefault(Method $method, string $type, array $column): Method
    {
        if ($column['default'] === null) {
            return $method;
        }

        switch ($type) {
            case 'integer':
            case 'bigInteger':
            case 'mediumInteger':
            case 'smallInteger':
            case 'tinyInteger':
                $method = $this->chainDefaultForInteger($method, $column);
                break;
            case 'decimal':
            case 'float':
            case 'double':
                $method = $this->chainDefaultForDecimal($method, $column);
                break;
            case 'boolean':
                $method = $this->chainDefaultForBoolean($method, $column);
                break;
            case 'softDeletes':
            case 'datetime':
            case 'timestamp':
                $method = $this->chainDefaultForDatetime($method, $column);
                break;
            default:
                $method = $this->chainDefaultForString($method, $column);
        }
        return $method;
    }

    /**
     * Set default value to method for integer column.
     *
     */
    private function chainDefaultForInteger(Method $method, array $column): Method
    {
        $method->chain('default', (int) $column['default']);
        return $method;
    }

    /**
     * Set default value to method for decimal column.
     *
     */
    private function chainDefaultForDecimal(Method $method, array $column): Method
    {
        $method->chain('default', (float) $column['default']);
        return $method;
    }

    /**
     * Set default value to method for boolean column.
     *
     */
    private function chainDefaultForBoolean(Method $method, array $column): Method
    {
        $method->chain('default', ((int) $column['default']) === 1);
        return $method;
    }

    /**
     * Set default value to method for datetime column.
     *
     */
    private function chainDefaultForDatetime(Method $method, array $column): Method
    {
        switch ($column['default']) {
            case 'CURRENT_TIMESTAMP':
                    $method->chain('useCurrent');
                break;
            default:
                $method->chain('default', $column['default']);
        }

        return $method;
    }

    /**
     * Set default value to method, which support string.
     *
     */
    private function chainDefaultForString(Method $method, array $column): Method
    {
        $quotes  = '\'';
        $default = $column['default'];
        // To replace from ' to \\\'
        $method->chain('default', str_replace($quotes, '\\\\'.$quotes, $default));

        return $method;
    }
}
