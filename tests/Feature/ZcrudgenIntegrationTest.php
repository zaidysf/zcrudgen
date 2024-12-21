<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Feature;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class ZcrudgenIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createTestDirectories();

        // Create test tables
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function test_complete_crud_generation(): void
    {
        $this->artisan('zcrudgen:make', [
            'name' => 'User',
            '--permissions' => true,
            '--middleware' => 'auth:sanctum',
        ])->assertSuccessful();

        // Verify all files were generated
        $this->assertFileExists(base_path('app/Http/Controllers/API/UserController.php'));
        $this->assertFileExists(base_path('app/Models/User.php'));
        $this->assertFileExists(base_path('app/Services/UserService.php'));
        $this->assertFileExists(base_path('app/Repositories/UserRepository.php'));
        $this->assertFileExists(base_path('tests/Feature/Api/UserControllerTest.php'));

        // Verify route was added
        require base_path('routes/api.php'); // Reload routes
        $this->assertTrue(Route::has('users.index'));

        // Verify OpenAPI documentation exists in controller
        $controllerContent = file_get_contents(base_path('app/Http/Controllers/API/UserController.php'));
        $this->assertStringContainsString('@openapi', $controllerContent);
    }

    public function test_crud_generation_with_new_migration(): void
    {
        // Drop the test table first
        Schema::dropIfExists('products');

        $this->artisan('zcrudgen:make', [
            'name' => 'Product',
        ])->assertSuccessful();

        // Verify migration was created
        $migrationFiles = glob(database_path('migrations/*_create_products_table.php'));
        $this->assertNotEmpty($migrationFiles);

        // Verify migration content
        $migrationContent = file_get_contents($migrationFiles[0]);
        $this->assertStringContainsString('class CreateProductsTable extends Migration', $migrationContent);
    }

    protected function tearDown(): void
    {
        // Clean up test files
        $this->cleanDirectory(base_path('app/Http/Controllers/API'));
        $this->cleanDirectory(base_path('app/Models'));
        $this->cleanDirectory(base_path('app/Services'));
        $this->cleanDirectory(base_path('app/Repositories'));
        $this->cleanDirectory(base_path('tests/Feature/Api'));
        $this->cleanDirectory(database_path('migrations'));

        Schema::dropIfExists('users');
        Schema::dropIfExists('products');

        parent::tearDown();
    }

    protected function cleanDirectory(string $path): void
    {
        if (! is_dir($path)) {
            return;
        }

        $files = glob($path . '/*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
