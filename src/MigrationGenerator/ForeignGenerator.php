<?php

namespace Pentacom\Repgenerator\MigrationGenerator;

use MigrationsGenerator\Generators\MigrationConstants\Method\Foreign;
use Pentacom\Repgenerator\MigrationGenerator\Blueprint\Method;
use Pentacom\Repgenerator\MigrationGenerator\Blueprint\Table;

/**
 * Class ForeignGenerator
 */
class ForeignGenerator
{
    /**
     * Converts index into migration method.
     *
     */
    public function generate(array $foreign): Method
    {
        $method = new Method('foreign', $foreign['column']);

        if($foreign['reference'] !== null && $foreign['on'] !== null) {
            $method->chain('references', $foreign['reference']);
            $method->chain('on', $foreign['on']);
        }

        if ($foreign['onUpdate'] !== null) {
            $method->chain('onUpdate', $foreign['onUpdate']);
        }

        if ($foreign['onDelete'] !== null) {
            $method->chain('onDelete', $foreign['onDelete']);
        }

        return $method;
    }

    /**
     * Generates drop foreign migration method.
     *
     */
    public function generateDrop(array $foreign, Table $table): Method
    {
        $foreignName = $table->getName().'_'.$foreign['column'].'_foreign';

        return new Method('dropForeign', $foreignName);
    }
}
