<?php

namespace Pentacom\Repgenerator\MigrationGenerator\ColumnModifiers;

use Pentacom\Repgenerator\MigrationGenerator\Blueprint\Method;

/**
 * Class CommentColumnModifier
 */
class CommentColumnModifier
{
    /**
     * Set comment.
     *
     */
    public function chainComment(Method $method, array $column): Method
    {
        if ($column['comment'] !== null) {
            $method->chain('comment', $column['comment']);
        }
        return $method;
    }
}
