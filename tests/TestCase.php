<?php

namespace ZaidYasyaf\Zcrudgen\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use ZaidYasyaf\Zcrudgen\ZcrudgenServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            ZcrudgenServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');
    }
}
