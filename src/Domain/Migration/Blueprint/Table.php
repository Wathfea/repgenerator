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
        return Str::lower(str_replace(' ', '_',Str::plural($this->name)));
    }

    /**
     * @param  string  $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }


}
