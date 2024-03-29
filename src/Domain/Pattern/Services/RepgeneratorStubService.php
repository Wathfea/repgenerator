<?php

namespace Pentacom\Repgenerator\Domain\Pattern\Services;


/**
 * Class RepgeneratorStubService
 */
class RepgeneratorStubService
{
    public function __construct(protected string $stubsLocation)
    {

    }

    /**
     * @param  string  $name
     * @return false|string
     */
    public function getFilterStub(string $name): bool|string
    {
        return file_get_contents($this->stubsLocation.'/Filter/'.$name.'.stub');
    }

    /**
     * @param  string  $name
     * @return false|string
     */
    public function getStub(string $name): bool|string
    {
        return file_get_contents($this->stubsLocation.$name.".stub");
    }

    /**
     * @param string $name
     * @return bool
     */
    public function doesStubExist(string $name): bool {
        return file_exists($this->stubsLocation.$name.".stub");
    }
}
