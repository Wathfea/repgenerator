<?php

namespace Pentacom\Repgenerator\Domain\Migration\Blueprint;

use Pentacom\Repgenerator\Domain\Migration\Blueprint\Traits\Stringable;
use Pentacom\Repgenerator\Domain\Migration\Writer\Constants;

/**
 * Class SchemaBlueprint
 */
class SchemaBlueprint implements ToStringInterface
{
    use Stringable;

    /** @var string */
    private $table;

    /** @var string */
    private $schemaBuilder;

    /** @var TableBlueprint|null */
    private $blueprint;

    /**
     * SchemaBlueprint constructor.
     *
     * @param  string  $table  Table name.
     * @param  string  $schemaBuilder  SchemaBuilder name.
     */
    public function __construct(string $table, string $schemaBuilder)
    {
        $this->table         = $table;
        $this->schemaBuilder = $schemaBuilder;
        $this->blueprint     = null;
    }

    /**
     * @param  TableBlueprint  $blueprint
     */
    public function setBlueprint(TableBlueprint $blueprint): void
    {
        $this->blueprint = $blueprint;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        $schema = "Schema::$this->schemaBuilder";

        $lines = [];
        if ($this->blueprint !== null) {
            $lines[] = "$schema('$this->table', function (Blueprint \$table) {";
            // Add 1 tabulation to indent blueprint definition.
            $lines[] = Constants::TAB.$this->blueprint->toString();
            $lines[] = "});";
        } else {
            $lines[] = "$schema('$this->table');";
        }

        return $this->implodeLines($lines, 2);
    }
}
