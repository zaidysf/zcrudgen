<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

class RequestGenerator extends BaseGenerator
{
    public function generate(string $name, array $columns): array
    {
        $requestPath = config('zcrudgen.paths.request', app_path('Http/Requests'));
        $className = $this->studlyCase($name);

        $paths = [
            'create' => $this->generateCreateRequest($requestPath, $className, $columns),
            'update' => $this->generateUpdateRequest($requestPath, $className, $columns),
        ];

        return $paths;
    }

    protected function generateCreateRequest(string $path, string $className, array $columns): string
    {
        $replacements = [
            '{{ namespace }}' => config('zcrudgen.namespace') . '\\Http\\Requests',
            '{{ class }}' => $className,
            '{{ rules }}' => $this->generateRules($columns, 'create'),
        ];

        $content = $this->generateClass('request.create', $replacements);
        $filePath = $path . '/Create' . $className . 'Request.php';

        $this->put($filePath, $content);

        return $filePath;
    }

    protected function generateUpdateRequest(string $path, string $className, array $columns): string
    {
        $replacements = [
            '{{ namespace }}' => config('zcrudgen.namespace') . '\\Http\\Requests',
            '{{ class }}' => $className,
            '{{ rules }}' => $this->generateRules($columns, 'update'),
        ];

        $content = $this->generateClass('request.update', $replacements);
        $filePath = $path . '/Update' . $className . 'Request.php';

        $this->put($filePath, $content);

        return $filePath;
    }

    protected function generateRules(array $columns, string $type): string
    {
        $rules = [];
        foreach ($columns as $column) {
            if (in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $rules[] = $this->generateRule($column, $type);
        }

        return implode(",\n            ", $rules);
    }

    protected function generateRule(string $column, string $type): string
    {
        $rules = ["'required'"];

        if (str_ends_with($column, '_id')) {
            $rules[] = "'exists:" . str_replace('_id', 's,id', $column) . "'";
        } elseif (str_contains($column, 'email')) {
            $rules[] = "'email'";
        } elseif (str_contains($column, 'password')) {
            $rules = $type === 'create' ? ["'required'", "'min:8'"] : ["'sometimes'", "'min:8'"];
        }

        return "'{$column}' => [" . implode(', ', $rules) . ']';
    }
}
