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
            '{{ namespace }}' => config('zcrudgen.namespace') . '\\Http\\Controllers\\API',
            '{{ class }}' => $className,
            '{{ model_namespace }}' => config('zcrudgen.namespace') . '\\Models\\' . $className,
            '{{ service_namespace }}' => config('zcrudgen.namespace') . '\\Services\\' . $className . 'Service',
            '{{ resource_namespace }}' => config('zcrudgen.namespace') . '\\Http\\Resources\\' . $className . 'Resource',
            '{{ request_namespace }}' => config('zcrudgen.namespace') . '\\Http\\Requests',
            '{{ route_prefix }}' => Str::kebab(Str::pluralStudly($name)),
            '{{ middleware }}' => $this->generateMiddleware($middleware, $usePermissions, $name),
            '{{ permissions }}' => $this->generatePermissions($usePermissions, $name),
        ];

        $content = $this->generateClass('controller', $replacements);
        $path = $controllerPath . '/' . $className . 'Controller.php';

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
            "\$this->middleware(['" . implode("', '", $middlewares) . "']);";
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
}
