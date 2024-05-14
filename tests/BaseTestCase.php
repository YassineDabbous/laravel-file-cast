<?php

namespace YassineDabbous\FileCast\Tests;

use Orchestra\Testbench\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/migrations');
    }

    
    protected function getEnvironmentSetUp($app) : void
    {
        // enable auto delete
        $app['config']->set('file-cast.auto_delete', true);
    }
}