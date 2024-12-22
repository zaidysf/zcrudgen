<?php

namespace ZaidYasyaf\Zcrudgen\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ZaidYasyaf\Zcrudgen\Generators\ControllerGenerator;
use ZaidYasyaf\Zcrudgen\Generators\MigrationGenerator;
use ZaidYasyaf\Zcrudgen\Generators\ModelGenerator;
use ZaidYasyaf\Zcrudgen\Generators\RepositoryGenerator;
use ZaidYasyaf\Zcrudgen\Generators\RequestGenerator;
use ZaidYasyaf\Zcrudgen\Generators\ResourceGenerator;
use ZaidYasyaf\Zcrudgen\Generators\RouteGenerator;
use ZaidYasyaf\Zcrudgen\Generators\ServiceGenerator;
use ZaidYasyaf\Zcrudgen\Generators\TestGenerator;

class ZcrudgenCommand extends Command
{
    protected $signature = 'zcrudgen:make {name?} {--relations=} {--middleware=} {--permissions}';

    protected $description = 'Generate CRUD API with advanced features';

    public function handle(): int
    {
        try {
            $this->info('Welcome to ZCrudGen! 🚀');
            $this->info('Let\'s create your CRUD API...');

            // Get model name
            $name = $this->argument('name');
            if (! $name) {
                $name = $this->ask('What is the name of your model?');
                if (empty($name)) {
                    $this->error('Model name is required.');

                    return self::FAILURE;
                }
            }

            $this->info("Generating CRUD for model: {$name}");
            $this->info('Base path: '.app()->basePath());

            // Get configurations
            $relations = $this->option('relations');
            $middleware = $this->option('middleware');
            $usePermissions = $this->option('permissions');

            // Create directories first
            $this->createDirectories();

            // Get table structure
            $tableName = Str::plural(Str::snake($name));
            $columns = Schema::hasTable($tableName)
                ? Schema::getColumnListing($tableName)
                : ['id', 'name', 'created_at', 'updated_at'];

            $steps = [
                'Migration' => fn () => $this->executeSafely('Migration', fn () => (new MigrationGenerator)->generate($name)),
                'Model' => fn () => $this->executeSafely('Model', fn () => $this->generateModel($name, $columns, $relations)),
                'Repository' => fn () => $this->executeSafely('Repository', fn () => $this->generateRepository($name)),
                'Service' => fn () => $this->executeSafely('Service', fn () => $this->generateService($name, $columns)),
                'Controller' => fn () => $this->executeSafely('Controller', fn () => $this->generateController($name, $middleware, $usePermissions)),
                'Requests' => fn () => $this->executeSafely('Requests', fn () => $this->generateRequests($name, $columns)),
                'Resource' => fn () => $this->executeSafely('Resource', fn () => $this->generateResource($name, $columns)),
                'Routes' => fn () => $this->executeSafely('Routes', fn () => (new RouteGenerator)->generate($name)),
                'Tests' => fn () => $this->executeSafely('Tests', fn () => (new TestGenerator)->generate($name, $columns)),
            ];

            foreach ($steps as $step => $callback) {
                $this->info("Generating {$step}...");
                $callback();
            }

            $this->info('CRUD generated successfully!');

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error('Critical error: '.$e->getMessage());
            $this->error('Stack trace:');
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }
    }

    private function executeSafely(string $step, callable $callback): mixed
    {
        try {
            return $callback();
        } catch (\Throwable $e) {
            $this->error("Error in {$step} generation:");
            $this->error($e->getMessage());
            $this->error("Stack trace for {$step}:");
            $this->error($e->getTraceAsString());

            throw $e;
        }
    }

    private function createDirectories(): void
    {
        $paths = [
            'app/Http/Controllers/API',
            'app/Models',
            'app/Services',
            'app/Repositories',
            'app/Repositories/Interfaces',
            'app/Http/Resources',
            'app/Http/Requests',
            'tests/Feature/Api',
        ];

        foreach ($paths as $path) {
            $fullPath = app()->basePath($path);
            if (! is_dir($fullPath)) {
                $this->info("Creating directory: {$fullPath}");
                if (! mkdir($fullPath, 0777, true)) {
                    throw new \RuntimeException("Failed to create directory: {$fullPath}");
                }
            }
        }
    }

    protected function generateModel(string $name, array $columns, ?string $relations): void
    {
        $generator = new ModelGenerator;
        $generator->generate($name, $columns, $relations);
    }

    protected function generateRepository(string $name): void
    {
        $generator = new RepositoryGenerator;
        $generator->generate($name);
    }

    protected function generateService(string $name, array $columns): void
    {
        $generator = new ServiceGenerator;
        $generator->generate($name, $columns);
    }

    protected function generateController(string $name, ?string $middleware, bool $usePermissions): void
    {
        $generator = new ControllerGenerator;
        $generator->generate($name, $middleware, $usePermissions);
    }

    protected function generateRequests(string $name, array $columns): void
    {
        $generator = new RequestGenerator;
        $generator->generate($name, $columns);
    }

    protected function generateResource(string $name, array $columns): void
    {
        $generator = new ResourceGenerator;
        $generator->generate($name, $columns);
    }
}
