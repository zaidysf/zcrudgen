<?php

namespace ZaidYasyaf\Zcrudgen\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use ZaidYasyaf\Zcrudgen\Generators\ControllerGenerator;
use ZaidYasyaf\Zcrudgen\Generators\MigrationGenerator;
use ZaidYasyaf\Zcrudgen\Generators\ModelGenerator;
use ZaidYasyaf\Zcrudgen\Generators\RepositoryGenerator;
use ZaidYasyaf\Zcrudgen\Generators\RequestGenerator;
use ZaidYasyaf\Zcrudgen\Generators\ResourceGenerator;
use ZaidYasyaf\Zcrudgen\Generators\RouteGenerator;
use ZaidYasyaf\Zcrudgen\Generators\ServiceGenerator;
use ZaidYasyaf\Zcrudgen\Generators\SwaggerGenerator;
use ZaidYasyaf\Zcrudgen\Generators\TestGenerator;

class ZcrudgenCommand extends Command
{
    protected $signature = 'zcrudgen:make {name?} {--relations=} {--middleware=} {--permissions}';

    protected $description = 'Generate CRUD API with advanced features';

    public function handle(): int
    {
        try {
            $name = $this->argument('name');
            $relations = $this->option('relations');
            $middleware = $this->option('middleware');
            $usePermissions = $this->option('permissions');

            $tableName = Str::plural(Str::snake($name));
            $columns = ['id', 'name', 'created_at', 'updated_at'];

            // Create test directories
            foreach ($this->getRequiredPaths() as $path) {
                if (! is_dir($path)) {
                    mkdir($path, 0777, true);
                }
            }

            // Generate components
            foreach ($this->getGenerators($name, $columns, $relations, $middleware, $usePermissions) as $type => $generator) {
                $this->info("Generating {$type}...");
                $generator();
            }

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function getRequiredPaths(): array
    {
        return [
            app_path('Http/Controllers/API'),
            app_path('Models'),
            app_path('Services'),
            app_path('Repositories'),
            app_path('Repositories/Interfaces'),
            app_path('Http/Resources'),
            app_path('Http/Requests'),
            base_path('tests/Feature/Api'),
            base_path('routes'),
        ];
    }

    private function getGenerators(string $name, array $columns, ?string $relations, ?string $middleware, bool $usePermissions): array
    {
        return [
            'Migration' => fn () => (new MigrationGenerator())->generate($name),
            'Model' => fn () => (new ModelGenerator())->generate($name, $columns, $relations),
            'Repository' => fn () => (new RepositoryGenerator())->generate($name),
            'Service' => fn () => (new ServiceGenerator())->generate($name, $columns),
            'Controller' => fn () => (new ControllerGenerator())->generate($name, $middleware, $usePermissions),
            'Requests' => fn () => (new RequestGenerator())->generate($name, $columns),
            'Resource' => fn () => (new ResourceGenerator())->generate($name, $columns),
            'Routes' => fn () => (new RouteGenerator())->generate($name),
            'Tests' => fn () => (new TestGenerator())->generate($name, $columns),
            'Swagger' => fn () => (new SwaggerGenerator())->generate($name, $columns),
        ];
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
