<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Unit\Generators;

use ZaidYasyaf\Zcrudgen\Generators\ControllerGenerator;
use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class ControllerGeneratorTest extends TestCase
{
    private ControllerGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new ControllerGenerator();
    }

    public function test_can_generate_controller_without_middleware(): void
    {
        $path = $this->generator->generate('User');

        $this->assertFileExists($path);
        $content = file_get_contents($path);

        $this->assertStringContainsString('class UserController extends Controller', $content);
        $this->assertStringContainsString('public function index()', $content);
        $this->assertStringContainsString('public function store(CreateUserRequest $request)', $content);
        $this->assertStringContainsString('public function update(UpdateUserRequest $request, int $id)', $content);
    }

    public function test_can_generate_controller_with_middleware(): void
    {
        $path = $this->generator->generate('User', 'auth:sanctum,verified');

        $content = file_get_contents($path);
        $this->assertStringContainsString("\$this->middleware(['auth:sanctum', 'verified']);", $content);
    }

    public function test_can_generate_controller_with_permissions(): void
    {
        $path = $this->generator->generate('User', null, true);

        $content = file_get_contents($path);
        $this->assertStringContainsString("'index' => 'read-user'", $content);
        $this->assertStringContainsString("'store' => 'create-user'", $content);
    }
}
