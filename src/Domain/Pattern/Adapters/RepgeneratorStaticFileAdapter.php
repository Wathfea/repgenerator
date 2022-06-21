<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Adapters;


use JetBrains\PhpStorm\ArrayShape;

/**
 * Class RepgeneratorStaticFileAdapter
 */
class RepgeneratorStaticFileAdapter
{

    /**
     * @param string $name
     * @param string $path
     */
    public function __construct(
        public string $name,
        public string $path,
    ) {

    }

    /**
     * @return array
     */
    #[ArrayShape(['name' => "string", 'path' => "string"])]
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'path' => $this->path,
        ];
    }
}
