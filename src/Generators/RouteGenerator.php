<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Str;

class RouteGenerator extends BaseGenerator
{
    public function generate(string $name): string
    {
        $routePath = base_path('routes/api.php');
        $className = $this->studlyCase($name);
        $routePrefix = Str::plural(Str::kebab($name));

        // Check if route already exists
        $currentRoutes = file_get_contents($routePath);
        if (str_contains($currentRoutes, $routePrefix)) {
            return $routePath;
        }

        $newRoute = "\nRoute::apiResource('{$routePrefix}', App\\Http\\Controllers\\API\\{$className}Controller::class);";

        // Append route to api.php
        file_put_contents($routePath, $newRoute, FILE_APPEND);

        return $routePath;
    }
}
