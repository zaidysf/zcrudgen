<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Str;

class ModelGenerator extends BaseGenerator
{
    public function generate(string $name, array $columns, ?string $relations = null): string
    {
        $modelPath = config('zcrudgen.paths.model', app_path('Models'));
        $className = $this->studlyCase($name);

        $replacements = [
            '{{ namespace }}' => config('zcrudgen.namespace') . '\\Models',
            '{{ class }}' => $className,
            '{{ fillable }}' => $this->generateFillable($columns),
            '{{ relations }}' => $this->generateRelations($relations),
            '{{ table }}' => Str::snake(Str::pluralStudly($name)),
        ];

        $content = $this->generateClass('model', $replacements);
        $path = $modelPath . '/' . $className . '.php';

        $this->put($path, $content);

        return $path;
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

        return "\n    " . implode("\n\n    ", $relationMethods);
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
