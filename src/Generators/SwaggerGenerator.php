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

        $tableName = Str::plural(Str::snake($name));

        $replacements = [
            '{{ class }}' => $name,
            '{{ table }}' => $tableName,
            '{{ parameters }}' => $this->generateParameters($columns),
            '{{ properties }}' => $this->generateProperties($columns),
        ];

        $content = $this->generateClass('swagger', $replacements);
        $path = storage_path("api-docs/{$tableName}.yaml");

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
            $parameters[] = $this->formatParameter($column);
        }

        return implode("\n", $parameters);
    }

    protected function formatParameter(string $column): string
    {
        return "      - name: {$column}\n        in: query\n        schema:\n          type: string";
    }

    protected function generateProperties(array $columns): string
    {
        $properties = [];
        foreach ($columns as $column) {
            if (in_array($column, ['created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }
            $properties[] = "      {$column}:\n        type: string";
        }

        return implode("\n", $properties);
    }

    protected function generateRequestSchema(array $columns, string $tableName): string
    {
        $properties = [];
        foreach ($columns as $column) {
            if (in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }
            $info = $this->getColumnInfo($tableName, $column);
            $definition = $this->getColumnDefinition($column, $info);
            $properties[] = $this->formatProperty($column, $definition['openapi'], $info);
        }

        return implode("\n", $properties);
    }

    protected function generateSchema(array $columns, string $tableName): string
    {
        $properties = [];
        foreach ($columns as $column) {
            $info = $this->getColumnInfo($tableName, $column);
            $definition = $this->getColumnDefinition($column, $info);
            $properties[] = $this->formatProperty($column, $definition['openapi'], $info);
        }

        return implode("\n", $properties);
    }

    protected function formatProperty(string $column, array $type, array $info): string
    {
        $schema = [];
        $schema[] = "type: {$type['type']}";

        if (isset($type['format'])) {
            $schema[] = "format: {$type['format']}";
        }

        if (isset($type['maxLength'])) {
            $schema[] = "maxLength: {$type['maxLength']}";
        }

        if (isset($type['precision'])) {
            $schema[] = "precision: {$type['precision']}";
            $schema[] = "scale: {$type['scale']}";
        }

        if ($info['nullable']) {
            $schema[] = 'nullable: true';
        }

        return "      {$column}:\n        ".implode("\n        ", $schema);
    }
}
