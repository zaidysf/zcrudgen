<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Unit\Generators;

use ZaidYasyaf\Zcrudgen\Generators\RouteGenerator;
use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class RouteGeneratorTest extends TestCase
{
    protected RouteGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new RouteGenerator;
    }

    public function test_can_generate_api_route(): void
    {
        // Create a temporary api.php
        $routePath = base_path('routes/api.php');
        $this->makeDirectory(dirname($routePath));
        file_put_contents($routePath, '<?php');

        $path = $this->generator->generate('User');

        $this->assertFileExists($path);
        $content = file_get_contents($path);
        $this->assertStringContainsString(
            "Route::apiResource('users', App\\Http\\Controllers\\API\\UserController::class);",
            $content
        );
    }

    public function test_does_not_duplicate_existing_route(): void
    {
        $routePath = base_path('routes/api.php');
        $this->makeDirectory(dirname($routePath));
        file_put_contents($routePath, "<?php\nRoute::apiResource('users', App\\Http\\Controllers\\API\\UserController::class);");

        $path = $this->generator->generate('User');

        $content = file_get_contents($path);
        $this->assertEquals(1, substr_count($content, "Route::apiResource('users'"));
    }
}
