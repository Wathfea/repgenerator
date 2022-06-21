<?php

namespace Pentacom\Repgenerator\MigrationGenerator\ColumnGenerators;

use Pentacom\Repgenerator\MigrationGenerator\Blueprint\Method;

/**
 * Class DatetimeColumnGenerator
 */
class DatetimeColumnGenerator
{
    private const MIGRATION_DEFAULT_PRECISION = 0;

    /**
     * @param  string  $type
     * @param  array  $column
     * @return Method
     */
    public function generate(string $type, array $column): Method
    {
        $precision = $column['precision'] ?? self::MIGRATION_DEFAULT_PRECISION;

        switch ($column['name']) {
            case 'deleted_at':
                if ($precision !== null) {
                    $method = new Method('soft_delete', 'deleted_at', $precision);
                } else {
                    $method = new Method('soft_delete');
                }
                break;
            default:
                if ($precision !== null) {
                    $method = new Method($type, $column['name'], $precision);
                } else {
                    $method = new Method($type, $column['name']);
                }
        }

        return $method;
    }
}
