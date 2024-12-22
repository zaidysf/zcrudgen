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
        $this->generator = new SwaggerGenerator();
    }

    public function test_can_generate_swagger_documentation(): void
    {
        config()->set('zcrudgen.swagger.enabled', true);

        $columns = ['id', 'name', 'email', 'status', 'created_at'];
        $path = $this->generator->generate('User', $columns);

        $this->assertFileExists($path);
        $content = file_get_contents($path);

        // Check API endpoints
        $this->assertStringContainsString('/api/users:', $content);
        $this->assertStringContainsString('/api/users/{id}:', $content);

        // Check schema properties
        $this->assertStringContainsString('id:', $content);
        $this->assertStringContainsString('name:', $content);
        $this->assertStringContainsString('email:', $content);
        $this->assertStringContainsString('status:', $content);

        // Check request body schema
        $this->assertStringContainsString('UserInput:', $content);
        $this->assertStringContainsString('required:', $content);
    }

    public function test_does_not_generate_when_disabled(): void
    {
        config()->set('zcrudgen.swagger.enabled', false);
        $path = $this->generator->generate('User', ['id', 'name']);
        $this->assertEmpty($path);
    }
}
