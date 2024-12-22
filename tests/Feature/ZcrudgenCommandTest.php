<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Feature;

use Illuminate\Support\Facades\File;
use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class ZcrudgenCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createBaseDirectories();
    }

    public function test_command_exists(): void
    {
        $this->artisan('zcrudgen:make')
            ->expectsQuestion('What is the name of your model?', 'User')
            // ->expectsConfirmation('Do you want to add relationships?', false)
            // ->expectsChoice('Select middleware to use', 'auth:sanctum', ['auth:sanctum', 'auth:api', 'auth', 'none'])
            // ->expectsConfirmation('Do you want to add permissions?', false)
            // ->expectsConfirmation('Do you want to proceed?', true)
            ->assertSuccessful();
    }

    public function test_can_generate_crud_for_simple_model(): void
    {
        $result = $this->artisan('zcrudgen:make', [
            'name' => 'User',
        ]);

        // Log the output for debugging
        $result->execute();

        // if ($result->getStatusCode() !== 0) {
        //     $this->fail("Command failed with output:\n" . $result->getDisplay());
        // }

        // Verify all files were generated
        $this->assertDirectoryExists($this->app->basePath('app/Http/Controllers/API'));
        $this->assertFileExists($this->app->basePath('app/Http/Controllers/API/UserController.php'));
        $this->assertFileExists($this->app->basePath('app/Models/User.php'));
        $this->assertFileExists($this->app->basePath('app/Services/UserService.php'));
        $this->assertFileExists($this->app->basePath('app/Repositories/UserRepository.php'));
        $this->assertFileExists($this->app->basePath('app/Repositories/Interfaces/UserRepositoryInterface.php'));
        $this->assertFileExists($this->app->basePath('tests/Feature/Api/UserControllerTest.php'));
        $this->assertFileExists($this->app->basePath('routes/api.php'));

        // Verify OpenAPI documentation
        $controllerContent = File::get($this->app->basePath('app/Http/Controllers/API/UserController.php'));
        $this->assertStringContainsString('@openapi', $controllerContent);
        $this->assertStringContainsString('tags: ["User"]', $controllerContent);

        // Verify route registration
        $routeContent = File::get($this->app->basePath('routes/api.php'));
        $this->assertStringContainsString("Route::apiResource('users'", $routeContent);

        // Verify test content
        $testContent = File::get($this->app->basePath('tests/Feature/Api/UserControllerTest.php'));
        $this->assertStringContainsString('class UserControllerTest extends TestCase', $testContent);
    }

    public function test_can_generate_crud_with_relationships(): void
    {
        $this->artisan('zcrudgen:make', [
            'name' => 'Post',
            '--relations' => 'user,category',
        ])->assertSuccessful();

        $modelContent = File::get($this->app->basePath('app/Models/Post.php'));
        $this->assertStringContainsString('public function user()', $modelContent);
        $this->assertStringContainsString('public function category()', $modelContent);
    }

    public function test_can_generate_crud_with_permissions(): void
    {
        $this->artisan('zcrudgen:make', [
            'name' => 'Product',
            '--permissions' => true,
        ])->assertSuccessful();

        $controllerContent = File::get($this->app->basePath('app/Http/Controllers/API/ProductController.php'));
        $this->assertStringContainsString('protected array $permissions = [', $controllerContent);
        $this->assertStringContainsString("'index' => 'read-product'", $controllerContent);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        $paths = [
            $this->app->basePath('app/Http/Controllers/API'),
            $this->app->basePath('app/Models'),
            $this->app->basePath('app/Services'),
            $this->app->basePath('app/Repositories'),
            $this->app->basePath('app/Http/Resources'),
            $this->app->basePath('app/Http/Requests'),
            $this->app->basePath('tests/Feature/Api'),
            $this->app->basePath('routes'),
        ];

        foreach ($paths as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
            }
        }

        parent::tearDown();
    }
}
