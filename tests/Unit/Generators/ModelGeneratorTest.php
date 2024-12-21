<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Unit\Generators;

use ZaidYasyaf\Zcrudgen\Generators\ModelGenerator;
use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class ModelGeneratorTest extends TestCase
{
    private ModelGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new ModelGenerator();
    }

    public function test_can_generate_simple_model(): void
    {
        $columns = ['id', 'name', 'email', 'created_at', 'updated_at'];
        $path = $this->generator->generate('User', $columns);

        $this->assertFileExists($path);
        $content = file_get_contents($path);

        $this->assertStringContainsString('class User extends Model', $content);
        $this->assertStringContainsString("'name'", $content);
        $this->assertStringContainsString("'email'", $content);
        $this->assertStringNotContainsString("'id'", $content);
        $this->assertStringNotContainsString("'created_at'", $content);
    }

    public function test_can_generate_model_with_relations(): void
    {
        $columns = ['id', 'country_id', 'name', 'created_at', 'updated_at'];
        $path = $this->generator->generate('City', $columns, 'country');

        $this->assertFileExists($path);
        $content = file_get_contents($path);

        $this->assertStringContainsString('public function country()', $content);
        $this->assertStringContainsString('return $this->belongsTo(\\Country::class);', $content);
    }
}
