<?php

namespace Pentacom\Repgenerator\Domain\Migration\Blueprint;

use Illuminate\Support\Collection;
use Pentacom\Repgenerator\Helpers\Constants;
use Pentacom\Repgenerator\Traits\Stringable;

/**
 * Class TableBlueprint
 */
class TableBlueprint
{
    use Stringable;

    /** @var Property[]|Method[]|string[] */
    private $lines;


    public function __construct()
    {
        $this->lines = [];
    }

    /**
     * @return Property[]|Method[]|string[]
     */
    public function getLines(): array
    {
        return $this->lines;
    }

    /**
     * @return Method|Property|string|null
     */
    public function removeLastLine()
    {
        return array_pop($this->lines);
    }

    /**
     *
     */
    public function setLineBreak(): void
    {
        $this->lines[] = Constants::LINE_BREAK;
    }

    /**
     * @param  Method  $method
     * @return Method
     */
    public function setMethod(Method $method): Method
    {
        $this->lines[] = $method;
        return $method;
    }

    /**
     * @param  string  $name  Method name.
     * @param  mixed  ...$values  Method arguments.
     * @return Method
     */
    public function setMethodByName(string $name, ...$values): Method
    {
        $method = new Method($name, ...$values);
        $this->lines[] = $method;
        return $method;
    }

    /**
     * @param  string  $name  Property name.
     * @param  mixed  $value
     * @return Property
     */
    public function setProperty(string $name, $value): Property
    {
        $property = new Property($name, $value);
        $this->lines[] = $property;
        return $property;
    }

    /**
     * @return string
     */
    public function toString(): string
    {
        $lines = [];
        foreach ($this->lines as $line) {
            switch (true) {
                case $line instanceof Property:
                    $lines[] = $this->propertyToString($line);
                    break;
                case $line instanceof Method:
                    $lines[] = $this->methodToString($line);
                    break;
                default:
                    $lines[] = $this->convertFromAnyTypeToString($line);
            }
        }

        return $this->implodeLines($lines, 3);
    }

    /**
     * Generates $table property, example:
     *
     * $table->collation = 'utf-8';
     * $table->test = false;
     * $table->test = true;
     * $table->test = null;
     * $table->test = [1, 'abc', true];
     *
     * @param  Property  $property
     * @return string
     */
    private function propertyToString(Property $property): string
    {
        $v = $this->convertFromAnyTypeToString($property->getValue());
        return '$table->'.$property->getName()." = $v;";
    }

    /**
     * Generates $table method with chains, example:
     *
     * $table->string('name', 100)->comment('Hello')->default('Test');
     *
     * @param  Method  $method
     * @return string
     */
    private function methodToString(Method $method): string
    {
        $methodStrings[] = $this->flattenMethod($method);
        if ($method->countChain() > 0) {
            foreach ($method->getChains() as $chain) {
                $methodStrings[] = $this->flattenMethod($chain);
            }
        }

        return '$table->'.implode('->', $methodStrings).";";
    }

    /**
     * Generates $table method, example:
     *
     * string('name', 100)
     * comment('Hello')
     * default('Test')
     *
     * @param  Method  $method
     * @return string
     */
    private function flattenMethod(Method $method): string
    {
        $v = (new Collection($method->getValues()))->map(function ($v) {
            return $this->convertFromAnyTypeToString($v);
        })->implode(', ');

        if ($secondParameter = $method->getSecondParameter()) {
            return $method->getName()."($v, '$secondParameter')";
        } else {
            return $method->getName()."($v)";
        }
    }
}
