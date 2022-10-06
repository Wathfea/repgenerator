<?php

namespace Pentacom\Repgenerator\Domain\Migration;

use Pentacom\Repgenerator\Domain\Migration\Blueprint\Method;
use Pentacom\Repgenerator\Domain\Migration\Blueprint\Table;

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
        $method = new Method('foreign', $foreign['parentTableColumn']);

        if ($foreign['referencedTable'] !== null && $foreign['referencedTableColumn'] !== null) {
            $method->chain('references', $foreign['referencedTableColumn']);
            $method->chain('on', $foreign['referencedTable']);
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
        $foreignName = $table->getName().'_'.$foreign['parentTableColumn'].'_foreign';

        return new Method('dropForeign', $foreignName);
    }
}
