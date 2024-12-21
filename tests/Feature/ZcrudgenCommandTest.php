<?php

namespace ZaidYasyaf\Zcrudgen\Tests\Feature;

use ZaidYasyaf\Zcrudgen\Tests\TestCase;

class ZcrudgenCommandTest extends TestCase
{
    public function test_command_exists(): void
    {
        $this->artisan('zcrudgen:make')
            ->expectsOutput('Please provide a model name.')
            ->assertExitCode(1);
    }

    public function test_can_generate_crud_for_simple_model(): void
    {
        $this->artisan('zcrudgen:make User')
            ->expectsOutput('CRUD generated successfully for User model!')
            ->assertExitCode(0);

        // Add assertions to check generated files
        $this->assertFileExists(app_path('Http/Controllers/API/UserController.php'));
        $this->assertFileExists(app_path('Models/User.php'));
        $this->assertFileExists(app_path('Services/UserService.php'));
        $this->assertFileExists(app_path('Repositories/UserRepository.php'));
    }
}
