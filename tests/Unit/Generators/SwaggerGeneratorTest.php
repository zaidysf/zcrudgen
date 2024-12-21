<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Unit\Generators;

use ZaidYasyaf\Zcrudgen\Generators\SwaggerGenerator;
use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class SwaggerGeneratorTest extends TestCase
{
    private SwaggerGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new SwaggerGenerator;

        // Enable Swagger in config
        config(['zcrudgen.swagger.enabled' => true]);
    }

    public function test_can_generate_swagger_documentation(): void
    {
        $columns = ['id', 'name', 'email', 'is_active', 'created_at'];
        $path = $this->generator->generate('User', $columns);

        $this->assertFileExists($path);
        $content = file_get_contents($path);

        // Test API endpoints
        $this->assertStringContainsString('/api/users:', $content);
        $this->assertStringContainsString('/api/users/{id}:', $content);

        // Test parameters
        $this->assertStringContainsString('name:', $content);
        $this->assertStringContainsString('email:', $content);
        $this->assertStringContainsString('is_active:', $content);

        // Test types
        $this->assertStringContainsString('type: string', $content);
        $this->assertStringContainsString('type: boolean', $content);
    }

    public function test_does_not_generate_when_disabled(): void
    {
        config(['zcrudgen.swagger.enabled' => false]);

        $columns = ['id', 'name'];
        $path = $this->generator->generate('User', $columns);

        $this->assertEmpty($path);
    }
}
