<?php

namespace Pentacom\Repgenerator\MigrationGenerator;

use Pentacom\Repgenerator\MigrationGenerator\Blueprint\Method;
use Pentacom\Repgenerator\MigrationGenerator\Blueprint\Table;
use Pentacom\Repgenerator\MigrationGenerator\ColumnGenerators\DatetimeColumnGenerator;
use Pentacom\Repgenerator\MigrationGenerator\ColumnGenerators\DecimalColumnGenerator;
use Pentacom\Repgenerator\MigrationGenerator\ColumnGenerators\DefaultColumnGenerator;
use Pentacom\Repgenerator\MigrationGenerator\ColumnGenerators\DoubleColumnGenerator;
use Pentacom\Repgenerator\MigrationGenerator\ColumnGenerators\EnumAndSetColumnGenerator;
use Pentacom\Repgenerator\MigrationGenerator\ColumnGenerators\IntegerColumnGenerator;
use Pentacom\Repgenerator\MigrationGenerator\ColumnGenerators\StringColumnGenerator;
use Pentacom\Repgenerator\MigrationGenerator\ColumnModifiers\CommentColumnModifier;
use Pentacom\Repgenerator\MigrationGenerator\ColumnModifiers\DefaultColumnModifier;
use Pentacom\Repgenerator\MigrationGenerator\ColumnModifiers\IndexColumnModifier;
use Pentacom\Repgenerator\MigrationGenerator\ColumnModifiers\NullableColumnModifier;
use Pentacom\Repgenerator\MigrationGenerator\ColumnModifiers\UnsignedColumnModifier;

/**
 * Class ColumnGenerator
 */
class ColumnGenerator
{
    /**
     * @param  StringColumnGenerator  $stringColumnGenerator
     * @param  IntegerColumnGenerator  $integerColumnGenerator
     * @param  DatetimeColumnGenerator  $datetimeColumnGenerator
     * @param  DecimalColumnGenerator  $decimalColumnGenerator
     * @param  DoubleColumnGenerator  $doubleColumnGenerator
     * @param  EnumAndSetColumnGenerator  $enumAndSetColumnGenerator
     * @param  DefaultColumnGenerator  $defaultColumnGenerator
     * @param  DefaultColumnModifier  $defaultColumnModifier
     * @param  NullableColumnModifier  $nullableColumnModifier
     * @param  CommentColumnModifier  $commentColumnModifier
     * @param  IndexColumnModifier  $indexColumnModifier
     * @param  UnsignedColumnModifier  $unsignedColumnModifier
     */
    public function __construct(
        private StringColumnGenerator $stringColumnGenerator,
        private IntegerColumnGenerator $integerColumnGenerator,
        private DatetimeColumnGenerator $datetimeColumnGenerator,
        private DecimalColumnGenerator $decimalColumnGenerator,
        private DoubleColumnGenerator $doubleColumnGenerator,
        private EnumAndSetColumnGenerator $enumAndSetColumnGenerator,
        private DefaultColumnGenerator $defaultColumnGenerator,
        private DefaultColumnModifier $defaultColumnModifier,
        private NullableColumnModifier $nullableColumnModifier,
        private CommentColumnModifier $commentColumnModifier,
        private IndexColumnModifier $indexColumnModifier,
        private UnsignedColumnModifier $unsignedColumnModifier
    )
    {
    }

    /**
     * @param  Table  $table
     * @param  array  $column
     * @return Method
     */
    public function generate(Table $table, array $column): Method
    {
        switch ($column['type']) {
            case 'integer':
            case 'bigInteger':
            case 'mediumInteger':
            case 'smallInteger':
            case 'tinyInteger':
                $method = $this->integerColumnGenerator->generate($column['type'], $column);
                break;
            case 'date':
            case 'dateTime':
            case 'dateTimeTz':
            case 'time':
            case 'timeTz':
            case 'timestamp':
            case 'timestampTz':
                $method = $this->datetimeColumnGenerator->generate($column['type'], $column);
                break;
            case 'decimal':
            case 'float':
                $method = $this->decimalColumnGenerator->generate($column['type'], $column);
                break;
            case 'double':
                $method = $this->doubleColumnGenerator->generate($column['type'], $column);
                break;
            case 'enum':
            case 'set':
                $method = $this->enumAndSetColumnGenerator->generate($column['type'], $column);
                break;
            case 'string':
                $method = $this->stringColumnGenerator->generate($column['type'], $column);
                break;
            default:
                $method = $this->defaultColumnGenerator->generate($column['type'], $column);
        }

        // $type may be changed after above `generate` operation, and the new type is stored as method name.
        // Refresh $type by get method name.
        $type = $method->getName();

        $method = $this->nullableColumnModifier->chainNullable($method, $type, $column);
        $method = $this->unsignedColumnModifier->chainUnsigned($method, $type, $column);
        $method = $this->defaultColumnModifier->chainDefault($method, $type, $column);
        $method = $this->commentColumnModifier->chainComment($method, $column);
        $method = $this->indexColumnModifier->chainIndex($method,$column);

        return $method;
    }
}
