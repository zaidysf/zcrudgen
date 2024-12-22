<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class ZcrudgenIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

        // Verify all files
        $this->assertAllFilesExist('User');
    }

    protected function assertAllFilesExist(string $name): void
    {
        $basePath = $this->app->basePath();

        $files = [
            "{$basePath}/app/Http/Controllers/API/{$name}Controller.php",
            "{$basePath}/app/Models/{$name}.php",
            "{$basePath}/app/Services/{$name}Service.php",
            "{$basePath}/app/Repositories/{$name}Repository.php",
            "{$basePath}/app/Repositories/Interfaces/{$name}RepositoryInterface.php",
            "{$basePath}/app/Http/Resources/{$name}Resource.php",
            "{$basePath}/app/Http/Requests/Create{$name}Request.php",
            "{$basePath}/app/Http/Requests/Update{$name}Request.php",
            "{$basePath}/tests/Feature/Api/{$name}ControllerTest.php",
        ];

        foreach ($files as $file) {
            $this->assertFileExists($file, "Failed to find {$file}");
        }
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');
        parent::tearDown();
    }
}
