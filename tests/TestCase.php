<?php

namespace ZaidYasyaf\Zcrudgen\Tests;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;
use ZaidYasyaf\Zcrudgen\ZcrudgenServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        // Create base directories first using native PHP functions
        $this->createBaseDirectories();

        parent::setUp();

        // Now we can use Laravel's features
        $this->createAppServiceProvider();
        $this->createBaseRoutes();
    }

    protected function createBaseDirectories(): void
    {
        $basePath = __DIR__.'/TestApp';

        $paths = [
            $basePath.'/app/Http/Controllers/API',
            $basePath.'/app/Models',
            $basePath.'/app/Services',
            $basePath.'/app/Repositories',
            $basePath.'/app/Repositories/Interfaces',
            $basePath.'/app/Http/Resources',
            $basePath.'/app/Http/Requests',
            $basePath.'/app/Providers',
            $basePath.'/tests/Feature/Api',
            $basePath.'/routes',
            $basePath.'/bootstrap/cache',
        ];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }
    }

    protected function createAppServiceProvider(): void
    {
        $providerPath = $this->app->basePath('app/Providers/AppServiceProvider.php');

        if (! file_exists($providerPath)) {
            $content = <<<PHP
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register()
    {
        //
    }

    public function boot()
    {
        //
    }
}
PHP;
            file_put_contents($providerPath, $content);
        }
    }

    protected function createBaseRoutes(): void
    {
        $routePath = $this->app->basePath('routes/api.php');

        if (! file_exists($routePath)) {
            $content = <<<'PHP'
<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api')->group(function () {
    // API Routes will be added here
});

PHP;
            file_put_contents($routePath, $content);
        }
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Set the app base path
        $app->setBasePath($this->getBasePath());

        // Configure database
        config()->set('database.default', 'testing');
        config()->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function getBasePath(): string
    {
        return __DIR__.'/TestApp';
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->getBasePath());
        parent::tearDown();
    }

    protected function deleteDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $items = scandir($path);
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            $fullPath = $path.DIRECTORY_SEPARATOR.$item;
            if (is_dir($fullPath)) {
                $this->deleteDirectory($fullPath);
            } else {
                unlink($fullPath);
            }
        }

        rmdir($path);
    }

    protected function getPackageProviders($app): array
    {
        return [
            ZcrudgenServiceProvider::class,
        ];
    }

    protected function makeDirectory(string $path): void
    {
        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true);
        }
    }
}
