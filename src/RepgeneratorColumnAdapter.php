<?php

namespace Pentacom\Repgenerator;


/**
 * Class RepgeneratorColumnAdapter
 */
class RepgeneratorColumnAdapter
{

    /**
     * @param  string  $name
     * @param  string  $type
     * @param  bool  $aic
     * @param  bool  $nullable
     * @param  bool  $cascade
     * @param  int|null  $length
     * @param  string|null  $comment
     * @param  int|null  $precision
     * @param  int|null  $scale
     * @param  bool  $unsigned
     * @param  array|null  $values
     * @param  string|null  $default
     * @param  array|null  $index
     */
    public function __construct(
        public string $name,
        public string $type,
        public bool $aic = false,
        public bool $nullable = false,
        public bool $cascade = false,
        public ?int $length = null,
        public ?string $comment = null,
        public ?int $precision = null,
        public ?int $scale = null,
        public bool $unsigned = false,
        public ?array $values = null,
        public ?string $default = null,
        public ?array $index = []
    ) {

    }
}
