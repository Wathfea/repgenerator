<?php

namespace Pentacom\Repgenerator\Tests;

use Orchestra\Testbench\TestCase;
use Pentacom\Repgenerator\RepgeneratorServiceProvider;

class ExampleTest extends TestCase
{

    protected function getPackageProviders($app)
    {
        return [RepgeneratorServiceProvider::class];
    }
    
    /** @test */
    public function true_is_true()
    {
        $this->assertTrue(true);
    }
}
