<?php

namespace Bluora\LaravelHtmlGenerator\Tests;

use Mockery;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    /**
     * Tear down test.
     *
     * @return void
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        Mockery::close();
    }
}
