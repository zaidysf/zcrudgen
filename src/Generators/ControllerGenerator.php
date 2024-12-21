<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Str;

class ControllerGenerator extends BaseGenerator
{
    public function generate(string $name, ?string $middleware = null, bool $usePermissions = false): string
    {
        $controllerPath = config('zcrudgen.paths.controller', app_path('Http/Controllers/API'));
        $className = $this->studlyCase($name);

        $replacements = [
            '{{ namespace }}' => config('zcrudgen.namespace').'\\Http\\Controllers\\API',
            '{{ class }}' => $className,
            '{{ model_namespace }}' => config('zcrudgen.namespace').'\\Models\\'.$className,
            '{{ service_namespace }}' => config('zcrudgen.namespace').'\\Services\\'.$className.'Service',
            '{{ resource_namespace }}' => config('zcrudgen.namespace').'\\Http\\Resources\\'.$className.'Resource',
            '{{ request_namespace }}' => config('zcrudgen.namespace').'\\Http\\Requests',
            '{{ route_prefix }}' => Str::kebab(Str::pluralStudly($name)),
            '{{ middleware }}' => $this->generateMiddleware($middleware, $usePermissions, $name),
            '{{ permissions }}' => $this->generatePermissions($usePermissions, $name),
        ];

        $content = $this->generateClass('controller', $replacements);
        $path = $controllerPath.'/'.$className.'Controller.php';

        $this->put($path, $content);

        return $path;
    }

    protected function generateMiddleware(?string $middleware, bool $usePermissions, string $name): string
    {
        $middlewares = [];

        if ($middleware) {
            $middlewares = explode(',', $middleware);
        } elseif (config('zcrudgen.auth.middleware.enabled', true)) {
            $middlewares = config('zcrudgen.auth.middleware.default', ['auth:sanctum']);
        }

        if ($usePermissions) {
            $permissionMiddleware = $this->generatePermissionMiddleware($name);
            $middlewares = array_merge($middlewares, $permissionMiddleware);
        }

        return empty($middlewares) ? '' :
            "\$this->middleware(['".implode("', '", $middlewares)."']);";
    }

    protected function generatePermissionMiddleware(string $name): array
    {
        $name = Str::kebab($name);

        return [
            "permission:create-{$name}",
            "permission:read-{$name}",
            "permission:update-{$name}",
            "permission:delete-{$name}",
        ];
    }

    protected function generatePermissions(bool $usePermissions, string $name): string
    {
        if (! $usePermissions) {
            return '';
        }

        $name = Str::kebab($name);

        return <<<PHP

    /**
     * Required permissions for accessing this resource
     */
    protected array \$permissions = [
        'index' => 'read-{$name}',
        'show' => 'read-{$name}',
        'store' => 'create-{$name}',
        'update' => 'update-{$name}',
        'destroy' => 'delete-{$name}',
    ];
PHP;
    }

    protected function generateOpenApiDocumentation(string $name, array $columns): string
    {
        $className = $this->studlyCase($name);
        $routePrefix = Str::plural(Str::kebab($name));

        return <<<PHP
        /**
         * @openapi
         * /api/{$routePrefix}:
         *   get:
         *     tags: ["{$className}"]
         *     summary: List all {$routePrefix}
         *     description: Get a list of all {$routePrefix} with optional filters
         *     parameters:
         *       - name: page
         *         in: query
         *         description: Page number
         *         required: false
         *         schema:
         *           type: integer
         *     responses:
         *       200:
         *         description: Successful operation
         *         content:
         *           application/json:
         *             schema:
         *               type: object
         *               properties:
         *                 data:
         *                   type: array
         *                   items:
         *                     type: object
         *                     properties:
    {$this->generateOpenApiProperties($columns)}
        *
        *   post:
        *     tags: ["{$className}"]
        *     summary: Create new {$name}
        *     description: Create a new {$name} with the provided data
        *     requestBody:
        *       required: true
        *       content:
        *         application/json:
        *           schema:
        *             type: object
        *             properties:
    {$this->generateOpenApiProperties($columns, true)}
        *     responses:
        *       201:
        *         description: Created successfully
        *
        * /api/{$routePrefix}/{{id}}:
        *   get:
        *     tags: ["{$className}"]
        *     summary: Get specific {$name}
        *     parameters:
        *       - name: id
        *         in: path
        *         required: true
        *         schema:
        *           type: integer
        *     responses:
        *       200:
        *         description: Successful operation
        *
        *   put:
        *     tags: ["{$className}"]
        *     summary: Update {$name}
        *     parameters:
        *       - name: id
        *         in: path
        *         required: true
        *         schema:
        *           type: integer
        *     requestBody:
        *       required: true
        *       content:
        *         application/json:
        *           schema:
        *             type: object
        *             properties:
    {$this->generateOpenApiProperties($columns, true)}
        *     responses:
        *       200:
        *         description: Updated successfully
        *
        *   delete:
        *     tags: ["{$className}"]
        *     summary: Delete {$name}
        *     parameters:
        *       - name: id
        *         in: path
        *         required: true
        *         schema:
        *           type: integer
        *     responses:
        *       200:
        *         description: Deleted successfully
        */
    PHP;
    }

    /**
     * Generate OpenAPI properties for schema documentation
     */
    protected function generateOpenApiProperties(array $columns, bool $isRequest = false): string
    {
        $properties = [];
        foreach ($columns as $column) {
            // Skip certain columns for request documentation
            if ($isRequest && in_array($column, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            $type = $this->getOpenApiType($column);
            $properties[] = $this->formatOpenApiProperty($column, $type, $isRequest);
        }

        return implode("\n", $properties);
    }

    /**
     * Determine OpenAPI type for a column
     */
    protected function getOpenApiType(string $column): string
    {
        return match (true) {
            $column === 'id' || str_ends_with($column, '_id') => 'integer',
            str_contains($column, 'is_') || str_contains($column, 'has_') => 'boolean',
            str_contains($column, 'price') || str_contains($column, 'amount') => 'number',
            str_contains($column, 'date') || in_array($column, ['created_at', 'updated_at']) => 'string',
            default => 'string'
        };
    }

    /**
     * Format a single OpenAPI property
     */
    protected function formatOpenApiProperty(string $column, string $type, bool $isRequest): string
    {
        $required = $isRequest && ! in_array($column, ['id', 'created_at', 'updated_at'])
            ? 'true'
            : 'false';

        $example = $this->getOpenApiExample($column, $type);

        return <<<PROPERTY
        *                       {$column}:
        *                         type: {$type}
        *                         required: {$required}
        *                         example: {$example}
    PROPERTY;
    }

    /**
     * Get example value for OpenAPI documentation
     */
    protected function getOpenApiExample(string $column, string $type): string
    {
        return match (true) {
            $column === 'id' || str_ends_with($column, '_id') => '1',
            $column === 'email' => 'user@example.com',
            $column === 'name' => 'John Doe',
            $column === 'password' => '********',
            str_contains($column, 'price') => '99.99',
            str_contains($column, 'is_') || str_contains($column, 'has_') => 'true',
            str_contains($column, 'date') || in_array($column, ['created_at', 'updated_at']) => '2024-01-01T00:00:00Z',
            default => 'example'
        };
    }
}
