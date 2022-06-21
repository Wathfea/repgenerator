<?php

namespace Pentacom\Repgenerator\Domain\Migration\Blueprint;

use Illuminate\Support\Str;

/**
 * Class Table
 */
class Table
{
    /**
     * @var string
     */
    public string $name;

    /**
     * @return string
     */
    public function getName(): string
    {
        $plural = Str::plural($this->name);
        $smallCase = Str::lower($plural);

        return $smallCase;
    }

    /**
     * @param  string  $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }


}
