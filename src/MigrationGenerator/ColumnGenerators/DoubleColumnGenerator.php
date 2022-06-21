<?php

namespace Pentacom\Repgenerator\MigrationGenerator\ColumnGenerators;

use Pentacom\Repgenerator\MigrationGenerator\Blueprint\Method;

/**
 * Class DoubleColumnGenerator
 */
class DoubleColumnGenerator
{
    /**
     * (10, 0) are default value of precision and scale
     */
    private const DOUBLE_EMPTY_PRECISION = 10;
    /**
     *
     */
    private const DOUBLE_EMPTY_SCALE     = 0;

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
        return $this->getDoublePrecisions($column['precision'], $column['scale']);
    }

    /**
     * Empty double precision and scale is (10, 0).
     * Return precision and scale if this column is not (10, 0).
     *
     * @param  int  $precision
     * @param  int  $scale
     * @return int[] [precision, scale]
     */
    private function getDoublePrecisions(int $precision, int $scale): array
    {
        if ($precision === self::DOUBLE_EMPTY_PRECISION && $scale === self::DOUBLE_EMPTY_SCALE) {
            return [];
        }

        return [$precision, $scale];
    }
}
