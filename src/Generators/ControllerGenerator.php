<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ControllerGenerator extends BaseGenerator
{
    public function generate(string $name, ?string $middleware = null, bool $usePermissions = false): string
    {
        $controllerPath = config('zcrudgen.paths.controller', app_path('Http/Controllers/API'));
        $className = $this->studlyCase($name);

        $replacements = [
            '{{ namespace }}' => config('zcrudgen.namespace') . '\\Http\\Controllers\\API',
            '{{ class }}' => $className,
            '{{ model_namespace }}' => config('zcrudgen.namespace') . '\\Models\\' . $className,
            '{{ service_namespace }}' => config('zcrudgen.namespace') . '\\Services\\' . $className . 'Service',
            '{{ resource_namespace }}' => config('zcrudgen.namespace') . '\\Http\\Resources\\' . $className . 'Resource',
            '{{ request_namespace }}' => config('zcrudgen.namespace') . '\\Http\\Requests',
            '{{ route_prefix }}' => Str::plural(Str::kebab($name)),
            '{{ middleware }}' => $this->generateMiddleware($middleware, $usePermissions, $name),
            '{{ permissions }}' => $this->generatePermissions($usePermissions, $name),
        ];

        $content = $this->generateClass('controller', $replacements);
        $path = $controllerPath . '/' . $className . 'Controller.php';

        $this->put($path, $content);

        // Bind interface in AppServiceProvider
        $this->updateAppServiceProvider($name);

        return $path;
    }

    protected function updateAppServiceProvider(string $name): void
    {
        $path = app_path('Providers/AppServiceProvider.php');

        if (! File::exists($path)) {
            return; // Skip if provider doesn't exist
        }

        $content = File::get($path);

        $bindStatement = "\n\t\t\$this->app->bind(\\App\\Repositories\\Interfaces\\{$name}RepositoryInterface::class, \\App\\Repositories\\{$name}Repository::class);";

        // Check if binding already exists
        if (str_contains($content, $bindStatement)) {
            return;
        }

        // Find the register method
        if (str_contains($content, 'public function register()')) {
            // Add binding after register method opening brace
            $content = preg_replace(
                '/(public function register\(\)[\s\n]*{)/',
                "$1{$bindStatement}",
                $content
            );
        } else {
            // Add register method with binding
            $content = str_replace(
                'class AppServiceProvider extends ServiceProvider',
                "class AppServiceProvider extends ServiceProvider\n{\n\tpublic function register()\n\t{{$bindStatement}\n\t}",
                $content
            );
        }

        File::put($path, $content);
    }

    protected function generateMiddleware(?string $middleware, bool $usePermissions, string $name): string
    {
        $middlewares = [];

        if ($middleware) {
            $middlewares = explode(',', $middleware);
        }

        if ($usePermissions) {
            $middlewares[] = 'permission:'.Str::kebab($name);
        }

        return empty($middlewares) ? '' : "\$this->middleware(['".implode("', '", $middlewares)."']);";
    }

    protected function generatePermissions(bool $usePermissions, string $name): string
    {
        if (! $usePermissions) {
            return '';
        }

        $name = Str::kebab($name);

        return <<<PHP

    protected array \$permissions = [
        'index' => 'read-{$name}',
        'show' => 'read-{$name}',
        'store' => 'create-{$name}',
        'update' => 'update-{$name}',
        'destroy' => 'delete-{$name}',
    ];
PHP;
    }

    protected function generateRequestProperties(array $columns): string
    {
        $properties = [];
        foreach ($columns as $column) {
            if (in_array($column, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            $type = $this->getPropertyType($column);
            $properties[] = $this->formatProperty($column, $type);
        }

        return implode("\n", $properties);
    }

    protected function getPropertyType(string $column): string
    {
        return match (true) {
            $column === 'id' || str_ends_with($column, '_id') => 'integer',
            str_contains($column, 'is_') || str_contains($column, 'has_') => 'boolean',
            str_contains($column, 'price') || str_contains($column, 'amount') => 'number',
            str_contains($column, 'date') || in_array($column, ['created_at', 'updated_at']) => 'string',
            default => 'string'
        };
    }

    protected function formatProperty(string $column, string $type): string
    {
        return <<<PROPERTY
                    {$column}:
                    type: {$type}
                    example: {$this->getExample($column, $type)}
    PROPERTY;
    }

    protected function getExample(string $column, string $type): string
    {
        return match (true) {
            $column === 'email' => 'user@example.com',
            $column === 'name' => 'John Doe',
            $column === 'password' => '********',
            str_contains($column, 'price') => '99.99',
            str_contains($column, 'is_') || str_contains($column, 'has_') => 'true',
            str_contains($column, 'date') => '2024-01-01T00:00:00Z',
            $type === 'integer' => '1',
            default => 'example'
        };
    }
}
