<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Str;

class ModelGenerator extends BaseGenerator
{
    public function generate(string $name, array $columns, ?string $relations = null): string
    {
        $tableName = Str::plural(Str::snake($name));

        $replacements = [
            '{{ namespace }}' => config('zcrudgen.namespace').'\\Models',
            '{{ class }}' => $name,
            '{{ table }}' => $tableName,
            '{{ fillable }}' => $this->generateFillable($columns),
            '{{ casts }}' => $this->generateCasts($columns, $tableName),
            '{{ timestamps }}' => $this->hasTimestamps($columns) ? '' : "\n    public \$timestamps = false;",
            '{{ relations }}' => $this->generateRelations($relations),
        ];

        $content = $this->generateClass('model', $replacements);
        $path = app_path("Models/{$name}.php");

        $this->put($path, $content);

        return $path;
    }

    protected function generateCasts(array $columns, string $tableName): string
    {
        $casts = [];
        foreach ($columns as $column) {
            $info = $this->getColumnInfo($tableName, $column);
            $definition = $this->getColumnDefinition($column, $info);
            if ($definition['cast']) {
                $casts[] = "'{$column}' => '{$definition['cast']}'";
            }
        }

        return empty($casts) ? '' : "\n    protected \$casts = [\n        ".
            implode(",\n        ", $casts)."\n    ];";
    }

    protected function hasTimestamps(array $columns): bool
    {
        return in_array('created_at', $columns) && in_array('updated_at', $columns);
    }

    protected function generateFillable(array $columns): string
    {
        $columns = array_filter($columns, function ($column) {
            return ! in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at']);
        });

        return implode(",\n        ", array_map(function ($column) {
            return "'$column'";
        }, $columns));
    }

    protected function generateRelations(?string $relations): string
    {
        if (empty($relations)) {
            return '';
        }

        $relationMethods = [];
        foreach (explode(',', $relations) as $relation) {
            $relationName = $this->camelCase($relation);
            $relationClass = $this->studlyCase($relation);

            $relationMethods[] = $this->generateRelationMethod($relationName, $relationClass);
        }

        return "\n    ".implode("\n\n    ", $relationMethods);
    }

    protected function generateRelationMethod(string $name, string $class): string
    {
        return <<<PHP
            public function {$name}()
            {
                return \$this->belongsTo(\\{$class}::class);
            }
        PHP;
    }
}
