<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Feature;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class ZcrudgenIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
            app_path('Http/Controllers/API'),
            app_path('Models'),
            app_path('Services'),
            app_path('Repositories'),
            app_path('Http/Resources'),
            app_path('Http/Requests'),
            storage_path('api-docs'),
        ];

        foreach ($paths as $path) {
            if (File::isDirectory($path)) {
                File::deleteDirectory($path);
            }
        }

        // Drop test table
        Schema::dropIfExists('users');

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
        $this->assertFileExists(app_path('Http/Controllers/API/UserController.php'));
        $this->assertFileExists(app_path('Models/User.php'));
        $this->assertFileExists(app_path('Services/UserService.php'));
        $this->assertFileExists(app_path('Repositories/UserRepository.php'));
        $this->assertFileExists(app_path('Repositories/Interfaces/UserRepositoryInterface.php'));
        $this->assertFileExists(app_path('Http/Resources/UserResource.php'));
        $this->assertFileExists(app_path('Http/Requests/CreateUserRequest.php'));
        $this->assertFileExists(app_path('Http/Requests/UpdateUserRequest.php'));

        if (config('zcrudgen.swagger.enabled')) {
            $this->assertFileExists(storage_path('api-docs/user.yaml'));
        }

        // Test file contents
        $controllerContent = File::get(app_path('Http/Controllers/API/UserController.php'));
        $this->assertStringContainsString('permission:create-user', $controllerContent);
        $this->assertStringContainsString('auth:sanctum', $controllerContent);

        $modelContent = File::get(app_path('Models/User.php'));
        $this->assertStringContainsString('protected $fillable', $modelContent);
        $this->assertStringContainsString("'name'", $modelContent);
        $this->assertStringContainsString("'email'", $modelContent);
    }

    public function test_crud_generation_with_relationships(): void
    {
        // Create related tables
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

        $modelContent = File::get(app_path('Models/City.php'));
        $this->assertStringContainsString('public function country()', $modelContent);
        $this->assertStringContainsString('return $this->belongsTo', $modelContent);

        // Clean up
        Schema::dropIfExists('cities');
        Schema::dropIfExists('countries');
    }
}
