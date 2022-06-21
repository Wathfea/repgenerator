<?php

namespace Pentacom\Repgenerator\Domain\Migration\ColumnModifiers;

use Pentacom\Repgenerator\Domain\Migration\Blueprint\Method;

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
