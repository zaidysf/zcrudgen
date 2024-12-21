<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class RouteGenerator extends BaseGenerator
{
    public function generate(string $name): string
    {
        $routePath = base_path('routes/api.php');
        $className = $this->studlyCase($name);
        $routePrefix = Str::plural(Str::kebab($name));

        // Check if route already exists
        $currentRoutes = File::get($routePath);
        if (str_contains($currentRoutes, $routePrefix)) {
            return $routePath;
        }

        $newRoute = "\nRoute::apiResource('{$routePrefix}', App\\Http\\Controllers\\API\\{$className}Controller::class);\n";

        // Append route to api.php
        File::append($routePath, $newRoute);

        return $routePath;
    }
}
