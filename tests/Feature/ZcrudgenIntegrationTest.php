<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class ZcrudgenIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Create test directories
        $this->createTestDirectories();

        // Create a test table
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        // Clean up generated files
        $paths = [
            $this->app->basePath('app/Http/Controllers/API'),
            $this->app->basePath('app/Models'),
            $this->app->basePath('app/Services'),
            $this->app->basePath('app/Repositories'),
            $this->app->basePath('app/Http/Resources'),
            $this->app->basePath('app/Http/Requests'),
        ];

        foreach ($paths as $path) {
            if (is_dir($path)) {
                $this->removeDirectory($path);
            }
        }

        // Drop test tables
        Schema::dropIfExists('users');
        Schema::dropIfExists('cities');
        Schema::dropIfExists('countries');

        parent::tearDown();
    }

    public function test_complete_crud_generation(): void
    {
        $this->artisan('zcrudgen:make', [
            'name' => 'User',
            '--permissions' => true,
            '--middleware' => 'auth:sanctum',
        ])->assertSuccessful();

        // Verify all files were generated
        $this->assertFileExists($this->app->basePath('app/Http/Controllers/API/UserController.php'));
        $this->assertFileExists($this->app->basePath('app/Models/User.php'));
        $this->assertFileExists($this->app->basePath('app/Services/UserService.php'));
        $this->assertFileExists($this->app->basePath('app/Repositories/UserRepository.php'));
        $this->assertFileExists($this->app->basePath('app/Repositories/Interfaces/UserRepositoryInterface.php'));
        $this->assertFileExists($this->app->basePath('app/Http/Resources/UserResource.php'));
        $this->assertFileExists($this->app->basePath('app/Http/Requests/CreateUserRequest.php'));
        $this->assertFileExists($this->app->basePath('app/Http/Requests/UpdateUserRequest.php'));
    }

    public function test_crud_generation_with_relationships(): void
    {
        // Create related tables first
        Schema::create('countries', function ($table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('cities', function ($table) {
            $table->id();
            $table->foreignId('country_id')->constrained();
            $table->string('name');
            $table->timestamps();
        });

        $this->artisan('zcrudgen:make', [
            'name' => 'City',
            '--relations' => 'country',
        ])->assertSuccessful();

        $this->assertFileExists($this->app->basePath('app/Models/City.php'));

        $modelContent = file_get_contents($this->app->basePath('app/Models/City.php'));
        $this->assertStringContainsString('public function country()', $modelContent);
        $this->assertStringContainsString('return $this->belongsTo', $modelContent);
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

    protected function removeDirectory($path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $files = array_diff(scandir($path), ['.', '..']);
        foreach ($files as $file) {
            $filePath = "$path/$file";
            is_dir($filePath) ? $this->removeDirectory($filePath) : unlink($filePath);
        }
        rmdir($path);
    }
}
