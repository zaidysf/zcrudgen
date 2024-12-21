<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Unit\Generators;

use Illuminate\Support\Facades\File;
use ZaidYasyaf\Zcrudgen\Generators\MigrationGenerator;
use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class MigrationGeneratorTest extends TestCase
{
    protected MigrationGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new MigrationGenerator();

        // Clear migrations directory
        File::deleteDirectory(database_path('migrations'));
        File::makeDirectory(database_path('migrations'), 0777, true);
    }

    public function test_can_generate_migration(): void
    {
        $columns = ['id', 'name', 'email', 'is_active', 'price', 'created_at', 'updated_at'];
        $path = $this->generator->generate('Product', $columns);

        $this->assertNotEmpty($path);
        $this->assertFileExists($path);
        $content = file_get_contents($path);

        $this->assertStringContainsString('class CreateProductsTable extends Migration', $content);
        $this->assertStringContainsString('$table->string(\'name\');', $content);
        $this->assertStringContainsString('$table->string(\'email\')->unique();', $content);
        $this->assertStringContainsString('$table->boolean(\'is_active\')->default(false);', $content);
        $this->assertStringContainsString('$table->decimal(\'price\', 10, 2);', $content);
    }

    public function test_can_generate_migration_with_foreign_keys(): void
    {
        $columns = ['id', 'category_id', 'name', 'created_at', 'updated_at'];
        $path = $this->generator->generate('Product', $columns);

        $this->assertNotEmpty($path);
        $this->assertFileExists($path);
        $content = file_get_contents($path);
        $this->assertStringContainsString('$table->foreignId(\'category_id\')->constrained(\'categories\');', $content);
    }

    public function test_does_not_generate_duplicate_migration(): void
    {
        $columns = ['id', 'name'];

        // Generate first migration
        $firstPath = $this->generator->generate('Test', $columns);
        $this->assertNotEmpty($firstPath);
        $this->assertFileExists($firstPath);

        // Try to generate again - should return the existing path
        $secondPath = $this->generator->generate('Test', $columns);
        $this->assertSame($firstPath, $secondPath);
    }

    protected function tearDown(): void
    {
        // Clean up migrations directory
        File::deleteDirectory(database_path('migrations'));
        parent::tearDown();
    }
}
