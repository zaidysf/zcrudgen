<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Unit\Generators;

use ZaidYasyaf\Zcrudgen\Generators\ResourceGenerator;
use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class ResourceGeneratorTest extends TestCase
{
    private ResourceGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new ResourceGenerator;
    }

    public function test_can_generate_resource(): void
    {
        $columns = ['id', 'name', 'email', 'password', 'created_at', 'updated_at'];
        $path = $this->generator->generate('User', $columns);

        $this->assertFileExists($path);
        $content = file_get_contents($path);

        $this->assertStringContainsString('class UserResource extends JsonResource', $content);
        $this->assertStringContainsString("'name' => \$this->name", $content);
        $this->assertStringContainsString("'email' => \$this->email", $content);
        $this->assertStringNotContainsString("'password'", $content);
    }
}
