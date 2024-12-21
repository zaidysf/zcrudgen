<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Str;

class SwaggerGenerator extends BaseGenerator
{
    public function generate(string $name, array $columns): string
    {
        if (! config('zcrudgen.swagger.enabled', true)) {
            return '';
        }

        $docPath = storage_path('api-docs');
        $className = $this->studlyCase($name);

        $replacements = [
            '{{ class }}' => $className,
            '{{ route_prefix }}' => Str::kebab(Str::pluralStudly($name)),
            '{{ parameters }}' => $this->generateParameters($columns),
            '{{ properties }}' => $this->generateProperties($columns),
        ];

        $content = $this->generateClass('swagger', $replacements);
        $path = $docPath.'/'.Str::kebab($className).'.yaml';

        $this->put($path, $content);

        return $path;
    }

    protected function generateParameters(array $columns): string
    {
        $parameters = [];
        foreach ($columns as $column) {
            if (in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $parameters[] = $this->generateParameter($column);
        }

        return implode("\n", $parameters);
    }

    protected function generateParameter(string $column): string
    {
        $type = $this->getColumnType($column);

        return <<<YAML
        - name: {$column}
          in: query
          schema:
            type: {$type}
          description: Filter by {$column}
YAML;
    }

    protected function generateProperties(array $columns): string
    {
        $properties = [];
        foreach ($columns as $column) {
            if ($column === 'password') {
                continue;
            }

            $properties[] = $this->generateProperty($column);
        }

        return implode("\n", $properties);
    }

    protected function generateProperty(string $column): string
    {
        $type = $this->getColumnType($column);

        return <<<YAML
        {$column}:
          type: {$type}
          example: {$this->getExample($column, $type)}
YAML;
    }

    protected function getColumnType(string $column): string
    {
        if (str_ends_with($column, '_id') || $column === 'id') {
            return 'integer';
        }
        if (in_array($column, ['created_at', 'updated_at', 'deleted_at'])) {
            return 'string';
        }
        if (str_contains($column, 'is_') || str_contains($column, 'has_')) {
            return 'boolean';
        }
        if (str_contains($column, 'price') || str_contains($column, 'amount')) {
            return 'number';
        }

        return 'string';
    }

    protected function getExample(string $column, string $type): string
    {
        switch ($type) {
            case 'integer':
                return '1';
            case 'boolean':
                return 'true';
            case 'number':
                return '99.99';
            case 'string':
                if (str_contains($column, 'email')) {
                    return 'user@example.com';
                }
                if (str_contains($column, 'date') || str_contains($column, '_at')) {
                    return '2024-01-01T00:00:00Z';
                }

                return 'Example '.Str::title(str_replace('_', ' ', $column));
        }
    }
}
