<?php

namespace ZaidYasyaf\Zcrudgen\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as Orchestra;
use ZaidYasyaf\Zcrudgen\ZcrudgenServiceProvider;

class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->createTestDirectories();
    }

    protected function getPackageProviders($app): array
    {
        return [
            ZcrudgenServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Setup package configuration
        $app['config']->set('zcrudgen.paths', [
            'model' => $app->basePath('app/Models'),
            'controller' => $app->basePath('app/Http/Controllers/API'),
            'repository' => $app->basePath('app/Repositories'),
            'service' => $app->basePath('app/Services'),
            'resource' => $app->basePath('app/Http/Resources'),
            'request' => $app->basePath('app/Http/Requests'),
        ]);
    }

    protected function setUpDatabase(): void
    {
        // Create tables if needed for tests
    }

    protected function createTestDirectories(): void
    {
        $paths = [
            $this->app->basePath('app/Http/Controllers/API'),
            $this->app->basePath('app/Models'),
            $this->app->basePath('app/Services'),
            $this->app->basePath('app/Repositories'),
            $this->app->basePath('app/Repositories/Interfaces'),
            $this->app->basePath('app/Http/Resources'),
            $this->app->basePath('app/Http/Requests'),
        ];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }
    }
}
