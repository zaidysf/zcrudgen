<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Unit\Generators;

use ZaidYasyaf\Zcrudgen\Generators\RepositoryGenerator;
use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class RepositoryGeneratorTest extends TestCase
{
    private RepositoryGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new RepositoryGenerator;
    }

    public function test_can_generate_repository_files(): void
    {
        $paths = $this->generator->generate('User');

        $this->assertFileExists($paths['interface']);
        $this->assertFileExists($paths['repository']);

        // Check interface content
        $interfaceContent = file_get_contents($paths['interface']);
        $this->assertStringContainsString('interface UserRepositoryInterface', $interfaceContent);

        // Check repository content
        $repositoryContent = file_get_contents($paths['repository']);
        $this->assertStringContainsString('class UserRepository implements UserRepositoryInterface', $repositoryContent);
    }
}
