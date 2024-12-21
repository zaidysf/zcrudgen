<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Unit\Generators;

use ZaidYasyaf\Zcrudgen\Generators\RequestGenerator;
use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class RequestGeneratorTest extends TestCase
{
    private RequestGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new RequestGenerator();
    }

    public function test_can_generate_create_and_update_requests(): void
    {
        $columns = ['id', 'name', 'email', 'password', 'created_at', 'updated_at'];
        $paths = $this->generator->generate('User', $columns);

        // Test Create Request
        $this->assertFileExists($paths['create']);
        $createContent = file_get_contents($paths['create']);
        $this->assertStringContainsString('class CreateUserRequest extends FormRequest', $createContent);
        $this->assertStringContainsString("'name' => ['required']", $createContent);
        $this->assertStringContainsString("'email' => ['required', 'email']", $createContent);
        $this->assertStringContainsString("'password' => ['required', 'min:8']", $createContent);

        // Test Update Request
        $this->assertFileExists($paths['update']);
        $updateContent = file_get_contents($paths['update']);
        $this->assertStringContainsString('class UpdateUserRequest extends FormRequest', $updateContent);
        $this->assertStringContainsString("'password' => ['sometimes', 'min:8']", $updateContent);
    }

    public function test_handles_foreign_key_columns(): void
    {
        $columns = ['id', 'user_id', 'title', 'created_at'];
        $paths = $this->generator->generate('Post', $columns);

        $createContent = file_get_contents($paths['create']);
        $this->assertStringContainsString("'user_id' => ['required', 'exists:users,id']", $createContent);
    }
}
