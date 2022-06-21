<?php

namespace Pentacom\Repgenerator\Domain\Migration;

use Pentacom\Repgenerator\Domain\Migration\Blueprint\Method;
use Pentacom\Repgenerator\Domain\Migration\Blueprint\Table;
use Pentacom\Repgenerator\Domain\Migration\ColumnGenerators\DatetimeColumnGenerator;
use Pentacom\Repgenerator\Domain\Migration\ColumnGenerators\DecimalColumnGenerator;
use Pentacom\Repgenerator\Domain\Migration\ColumnGenerators\DefaultColumnGenerator;
use Pentacom\Repgenerator\Domain\Migration\ColumnGenerators\DoubleColumnGenerator;
use Pentacom\Repgenerator\Domain\Migration\ColumnGenerators\EnumAndSetColumnGenerator;
use Pentacom\Repgenerator\Domain\Migration\ColumnGenerators\IntegerColumnGenerator;
use Pentacom\Repgenerator\Domain\Migration\ColumnGenerators\StringColumnGenerator;
use Pentacom\Repgenerator\Domain\Migration\ColumnModifiers\CommentColumnModifier;
use Pentacom\Repgenerator\Domain\Migration\ColumnModifiers\DefaultColumnModifier;
use Pentacom\Repgenerator\Domain\Migration\ColumnModifiers\IndexColumnModifier;
use Pentacom\Repgenerator\Domain\Migration\ColumnModifiers\NullableColumnModifier;
use Pentacom\Repgenerator\Domain\Migration\ColumnModifiers\UnsignedColumnModifier;

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
        $method = match ($column['type']) {
            'integer', 'bigInteger', 'mediumInteger', 'smallInteger', 'tinyInteger' => $this->integerColumnGenerator->generate($column['type'], $column),
            'date', 'dateTime', 'dateTimeTz', 'time', 'timeTz', 'timestamp', 'timestampTz' => $this->datetimeColumnGenerator->generate($column['type'], $column),
            'decimal', 'float' => $this->decimalColumnGenerator->generate($column['type'], $column),
            'double' => $this->doubleColumnGenerator->generate($column['type'], $column),
            'enum', 'set' => $this->enumAndSetColumnGenerator->generate($column['type'], $column),
            'string' => $this->stringColumnGenerator->generate($column['type'], $column),
            default => $this->defaultColumnGenerator->generate($column['type'], $column),
        };

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
