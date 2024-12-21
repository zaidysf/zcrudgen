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
    protected $signature = 'zcrudgen:make {name} {--relations=} {--middleware=} {--permissions}';

    protected $description = 'Generate CRUD API with advanced features';

    public function handle(): int
    {
        try {
            $name = $this->argument('name');
            if (empty($name)) {
                $this->error('Name cannot be empty');

                return self::FAILURE;
            }

            $relations = $this->option('relations');
            $middleware = $this->option('middleware');
            $usePermissions = $this->option('permissions');

            // Create directories if they don't exist
            $this->createDirectories();

            // Generate migration if needed
            $migrationGenerator = new MigrationGenerator();
            $migrationGenerator->generate($name);

            // Get table structure
            $tableName = Str::plural(Str::snake($name));
            $columns = Schema::hasTable($tableName)
                ? Schema::getColumnListing($tableName)
                : ['id', 'name', 'created_at', 'updated_at'];

            // Generate all components
            $this->generateModel($name, $columns, $relations);
            $this->generateRepository($name);
            $this->generateService($name, $columns);
            $this->generateController($name, $middleware, $usePermissions);
            $this->generateRequests($name, $columns);
            $this->generateResource($name, $columns);

            // Generate route if needed
            $routeGenerator = new RouteGenerator();
            $routeGenerator->generate($name);

            $testGenerator = new TestGenerator();
            $testGenerator->generate($name, $columns);

            $this->info('CRUD generated successfully for ' . $name . ' model!');

            return self::SUCCESS;

        } catch (\Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    protected function createDirectories(): void
    {
        $paths = [
            app_path('Http/Controllers/API'),
            app_path('Models'),
            app_path('Services'),
            app_path('Repositories'),
            app_path('Repositories/Interfaces'),
            app_path('Http/Resources'),
            app_path('Http/Requests'),
            base_path('routes'),
            database_path('migrations'),
        ];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                mkdir($path, 0777, true);
            }
        }
    }

    protected function generateModel(string $name, array $columns, ?string $relations): void
    {
        $generator = new ModelGenerator();
        $generator->generate($name, $columns, $relations);
    }

    protected function generateRepository(string $name): void
    {
        $generator = new RepositoryGenerator();
        $generator->generate($name);
    }

    protected function generateService(string $name, array $columns): void
    {
        $generator = new ServiceGenerator();
        $generator->generate($name, $columns);
    }

    protected function generateController(string $name, ?string $middleware, bool $usePermissions): void
    {
        $generator = new ControllerGenerator();
        $generator->generate($name, $middleware, $usePermissions);
    }

    protected function generateRequests(string $name, array $columns): void
    {
        $generator = new RequestGenerator();
        $generator->generate($name, $columns);
    }

    protected function generateResource(string $name, array $columns): void
    {
        $generator = new ResourceGenerator();
        $generator->generate($name, $columns);
    }
}
