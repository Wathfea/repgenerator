<?php

namespace Pentacom\Repgenerator\MigrationGenerator\ColumnGenerators;

use Pentacom\Repgenerator\MigrationGenerator\Blueprint\Method;

/**
 * Class DecimalColumnGenerator
 */
class DecimalColumnGenerator
{
    /**
     * (8, 2) are default value of decimal, float
     */
    private const DECIMAL_DEFAULT_PRECISION = 8;

    /**
     * (8, 2) are default value of decimal, float
     */
    private const DECIMAL_DEFAULT_SCALE     = 2;

    /**
     * @param  string  $type
     * @param  array  $column
     * @return Method
     */
    public function generate(string $type, array $column): Method
    {
        $precisions = $this->getPrecisions($column);

        $method = new Method($type, $column['name'], ...$precisions);

        if ($column['unsigned']) {
            $method->chain('unsigned');
        }

        return $method;
    }

    /**
     * @param  array  $column
     * @return int[] [precision, scale]
     */
    private function getPrecisions(array $column): array
    {
        return $this->getDecimalPrecisions($column['precision'], $column['scale']);
    }

    /**
     * Default decimal precision and scale is (8, 2).
     * Return precision and scale if this column is not (8, 2).
     *
     * @param  int  $precision
     * @param  int  $scale
     * @return int[] [precision, scale]
     */
    private function getDecimalPrecisions(int $precision, int $scale): array
    {
        if ($precision === self::DECIMAL_DEFAULT_PRECISION && $scale === self::DECIMAL_DEFAULT_SCALE) {
            return [];
        }

        if ($scale === self::DECIMAL_DEFAULT_SCALE) {
            return [$precision];
        }

        return [$precision, $scale];
    }
}
