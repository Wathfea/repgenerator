<?php

namespace Pentacom\Repgenerator\Domain\Migration\ColumnModifiers;

use Pentacom\Repgenerator\Domain\Migration\Blueprint\Method;

/**
 * Class NullableColumnModifier
 */
class NullableColumnModifier
{
    /**
     * Set nullable.
     *
     */
    public function chainNullable(Method $method, string $type, array $column): Method
    {
        if ($column['nullable']) {
            if ($this->shouldAddNotNullModifier($type)) {
                $method->chain('nullable', false);
            }

            if ($this->shouldAddNullableModifier($type)) {
                $method->chain('nullable');
            }

            return $method;
        }



        return $method;
    }

    /**
     * Check if column should add nullable, by check the $type.
     * `softDeletes`, `rememberToken`, `timestamps` type are skipped.
     *
     * @param  string  $type
     * @return bool
     */
    private function shouldAddNullableModifier(string $type): bool
    {
        return !in_array($type, ['softDeletes', 'rememberToken', 'timestamps']);
    }

    /**
     * Check if column should add nullable(false), by check the $type.
     * Only check `softDeletes`, `rememberToken`
     *
     * @param  string  $type
     * @return bool
     */
    private function shouldAddNotNullModifier(string $type): bool
    {
        if (!in_array($type, ['softDeletes', 'rememberToken'])) {
            return false;
        }

        return true;
    }
}
