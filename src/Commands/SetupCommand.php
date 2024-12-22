<?php

namespace ZaidYasyaf\Zcrudgen\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupCommand extends Command
{
    protected $signature = 'zcrudgen:setup';

    protected $description = 'Setup ZCrudGen in your Laravel application';

    public function handle(): int
    {
        $this->info('Setting up ZCrudGen...');

        // Install Scramble
        $this->installScramble();

        // Setup directories
        $this->setupDirectories();

        // Add routes
        $this->setupRoutes();

        $this->info('ZCrudGen setup completed! 🎉');

        return self::SUCCESS;
    }

    protected function installScramble(): void
    {
        $this->info('Installing Scramble...');

        // Install package
        $this->info('Running: composer require dedoc/scramble');
        shell_exec('composer require dedoc/scramble');

        // Publish config
        $this->info('Publishing Scramble configuration...');
        Artisan::call('vendor:publish', [
            '--provider' => 'Dedoc\Scramble\ScrambleServiceProvider',
        ]);

        // Add route to web.php
        $routePath = base_path('routes/web.php');
        $routeContent = file_get_contents($routePath);

        if (! str_contains($routeContent, 'Route::scramble')) {
            file_put_contents($routePath, "\nRoute::scramble();\n", FILE_APPEND);
        }
    }

    protected function setupDirectories(): void
    {
        $directories = [
            app_path('Http/Controllers/API'),
            app_path('Models'),
            app_path('Services'),
            app_path('Repositories'),
            app_path('Repositories/Interfaces'),
            app_path('Http/Resources'),
            app_path('Http/Requests'),
        ];

        foreach ($directories as $directory) {
            if (! is_dir($directory)) {
                mkdir($directory, 0755, true);
                $this->info("Created directory: $directory");
            }
        }
    }

    protected function setupRoutes(): void
    {
        $path = base_path('routes/api.php');
        $content = file_get_contents($path);

        if (! str_contains($content, 'API Routes for ZCrudGen')) {
            $apiRoutes = "\n// API Routes for ZCrudGen\nRoute::prefix('api')->group(function () {\n\t// Your generated routes will be placed here\n});\n";
            file_put_contents($path, $apiRoutes, FILE_APPEND);
        }
    }
}
