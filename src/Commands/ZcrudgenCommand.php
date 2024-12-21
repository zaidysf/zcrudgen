<?php

namespace ZaidYasyaf\Zcrudgen\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use ZaidYasyaf\Zcrudgen\Generators\ControllerGenerator;
use ZaidYasyaf\Zcrudgen\Generators\ModelGenerator;
use ZaidYasyaf\Zcrudgen\Generators\RepositoryGenerator;
use ZaidYasyaf\Zcrudgen\Generators\RequestGenerator;
use ZaidYasyaf\Zcrudgen\Generators\ResourceGenerator;
use ZaidYasyaf\Zcrudgen\Generators\ServiceGenerator;
use ZaidYasyaf\Zcrudgen\Generators\TestGenerator;

class ZcrudgenCommand extends Command
{
    protected $signature = 'zcrudgen:make {name} {--relations=} {--middleware=} {--permissions}';

    protected $description = 'Generate CRUD API with advanced features';

    public function handle(): int
    {
        $name = $this->argument('name');
        if (empty($name)) {
            throw new \InvalidArgumentException('Name cannot be empty');
        }

        $relations = $this->option('relations');
        $middleware = $this->option('middleware');
        $usePermissions = $this->option('permissions');

        // Get table name from model name
        $tableName = Str::lower(Str::plural($name));

        // Get table structure if table exists
        $columns = Schema::hasTable($tableName)
            ? Schema::getColumnListing($tableName)
            : ['id', 'name', 'created_at', 'updated_at']; // Default columns if table doesn't exist

        // Generate all components
        $this->generateModel($name, $columns, $relations);
        $this->generateRepository($name);
        $this->generateService($name, $columns);
        $this->generateController($name, $middleware, $usePermissions);
        $this->generateRequests($name, $columns);
        $this->generateResource($name, $columns);
        $this->generateTests($name, $columns);

        $this->info('CRUD generated successfully for '.$name.' model!');

        return self::SUCCESS;
    }

    protected function generateTests(string $name, array $columns): void
    {
        $generator = new TestGenerator();
        $generator->generate($name, $columns);
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
