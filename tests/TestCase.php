<?php

namespace ZaidYasyaf\Zcrudgen\Tests;

use Illuminate\Support\Facades\File;
use Orchestra\Testbench\TestCase as Orchestra;
use ZaidYasyaf\Zcrudgen\ZcrudgenServiceProvider;

class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTestDirectories();
        $this->setUpDatabase();
        $this->copyStubs();
        $this->createInitialRoutes();
    }

    protected function createInitialRoutes(): void
    {
        $routePath = base_path('routes/api.php');

        if (! File::exists($routePath)) {
            $content = <<<PHP
<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'api'], function () {
    // API routes will be added here
});
PHP;
            File::put($routePath, $content);
        }

        // Load the routes
        require $routePath;
    }

    protected function copyStubs(): void
    {
        $stubsPath = __DIR__ . '/../stubs';
        $testStubsPath = __DIR__ . '/stubs';

        if (! File::isDirectory($testStubsPath)) {
            File::copyDirectory($stubsPath, $testStubsPath);
        }
    }

    protected function createTestDirectories(): void
    {
        $paths = [
            app_path('Http/Controllers/API'),
            app_path('Models'),
            app_path('Services'),
            app_path('Repositories'),
            app_path('Repositories/Interfaces'),
            app_path('Http/Resources'),
            app_path('Http/Requests'),
            base_path('routes'),
            database_path('migrations'),
            base_path('tests/Feature/Api'),
        ];

        foreach ($paths as $path) {
            if (! File::isDirectory($path)) {
                File::makeDirectory($path, 0777, true);
            }
        }

        // Create routes file if it doesn't exist
        if (! File::exists(base_path('routes/api.php'))) {
            File::put(
                base_path('routes/api.php'),
                "<?php\n\nuse Illuminate\Support\Facades\Route;\n"
            );
        }
    }

    protected function getEnvironmentSetUp($app): void
    {
        config()->set('database.default', 'testing');

        config()->set('zcrudgen.paths', [
            'model' => app_path('Models'),
            'controller' => app_path('Http/Controllers/API'),
            'repository' => app_path('Repositories'),
            'service' => app_path('Services'),
            'resource' => app_path('Http/Resources'),
            'request' => app_path('Http/Requests'),
            'test' => base_path('tests/Feature/Api'),
        ]);
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
            File::makeDirectory($path, 0777, true, true);
        }
    }

    protected function cleanDirectory(string $path): void
    {
        if (! File::isDirectory($path)) {
            return;
        }

        File::cleanDirectory($path);
    }

    protected function setUpDatabase(): void
    {
        // Add any database setup if needed
    }
}
