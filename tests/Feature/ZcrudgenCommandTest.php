<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Feature;

use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class ZcrudgenCommandTest extends TestCase
{
    public function test_command_exists(): void
    {
        // We expect this to fail with the right message
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Not enough arguments (missing: "name")');

        $this->artisan('zcrudgen:make');
    }

    public function test_can_generate_crud_for_simple_model(): void
    {
        // Create test directories
        $this->createTestDirectories();

        $this->artisan('zcrudgen:make', ['name' => 'User'])
            ->expectsOutput('CRUD generated successfully for User model!')
            ->assertExitCode(0);

        // Add assertions to check generated files
        $this->assertDirectoryExists($this->app->basePath('app/Http/Controllers/API'));
        $this->assertFileExists($this->app->basePath('app/Http/Controllers/API/UserController.php'));
        $this->assertFileExists($this->app->basePath('app/Models/User.php'));
        $this->assertFileExists($this->app->basePath('app/Services/UserService.php'));
        $this->assertFileExists($this->app->basePath('app/Repositories/UserRepository.php'));
    }

    protected function createTestDirectories(): void
    {
        $paths = [
            $this->app->basePath('app/Http/Controllers/API'),
            $this->app->basePath('app/Models'),
            $this->app->basePath('app/Services'),
            $this->app->basePath('app/Repositories'),
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
