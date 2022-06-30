<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Adapters;


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
     * @param  bool|null  $showOnTable
     * @param  array|null  $references
     * @param  string|null  $foreign
     * @param  string|null  $fileUploadLocation
     * @param  bool|null  $isMultiFileUpload
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
        public ?array $index = [],
        public ?bool $showOnTable = false,
        public ?array $references = null,
        public ?string $foreign = null,
        public ?string $fileUploadLocation = null,
        public ?bool $isMultiFileUpload = false,
    ) {

    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'aic' => $this->aic,
            'nullable' => $this->nullable,
            'cascade' => $this->cascade,
            'length' => $this->length,
            'comment' => $this->comment,
            'precision' => $this->precision,
            'scale' => $this->scale,
            'unsigned' => $this->unsigned,
            'values' => $this->values,
            'default' => $this->default,
            'index' => $this->index,
            'references' => $this->references,
            'foreign' => $this->foreign,
        ];
    }
}
