<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Str;

class RequestGenerator extends BaseGenerator
{
    public function generate(string $name, array $columns): array
    {
        $tableName = Str::plural(Str::snake($name));

        return [
            'create' => $this->generateCreateRequest($name, $columns, $tableName),
            'update' => $this->generateUpdateRequest($name, $columns, $tableName),
        ];
    }

    protected function generateRules(array $columns, string $tableName, bool $isUpdate = false): string
    {
        $rules = [];

        foreach ($columns as $column) {
            if (in_array($column, ['id', 'created_at', 'updated_at', 'deleted_at'])) {
                continue;
            }

            $columnRules = $isUpdate ? ['sometimes'] : ['required'];

            // Add validation based on column name
            if ($column === 'email') {
                $columnRules[] = 'email';
            } elseif ($column === 'password') {
                $columnRules[] = 'min:8';
            } elseif (str_ends_with($column, '_id')) {
                $tableName = str_replace('_id', 's', $column);
                $columnRules[] = "exists:{$tableName},id";
            }

            $rules[] = "'{$column}' => [".implode(', ', array_map(fn ($rule) => "'$rule'", $columnRules)).']';
        }

        return implode(",\n            ", $rules);
    }

    protected function generateCreateRequest(string $name, array $columns, string $tableName): string
    {
        $replacements = [
            '{{ namespace }}' => config('zcrudgen.namespace').'\\Http\\Requests',
            '{{ class }}' => $name,
            '{{ rules }}' => $this->generateRules($columns, $tableName, false),
        ];

        $content = $this->generateClass('request.create', $replacements);
        $path = app_path("Http/Requests/Create{$name}Request.php");

        $this->put($path, $content);

        return $path;
    }

    protected function generateUpdateRequest(string $name, array $columns, string $tableName): string
    {
        $replacements = [
            '{{ namespace }}' => config('zcrudgen.namespace').'\\Http\\Requests',
            '{{ class }}' => $name,
            '{{ rules }}' => $this->generateRules($columns, $tableName, true),
        ];

        $content = $this->generateClass('request.update', $replacements);
        $path = app_path("Http/Requests/Update{$name}Request.php");

        $this->put($path, $content);

        return $path;
    }

    // protected function generateRule(string $column, string $type): string
    // {
    //     $type = $this->getColumnType($column, $info);
    //     $rules = ["'required'"];

    //     if (str_ends_with($column, '_id')) {
    //         $rules[] = "'exists:".str_replace('_id', 's,id', $column)."'";
    //     } elseif (str_contains($column, 'email')) {
    //         $rules[] = "'email'";
    //     } elseif (str_contains($column, 'password')) {
    //         $rules = $type === 'create' ? ["'required'", "'min:8'"] : ["'sometimes'", "'min:8'"];
    //     }

    //     return "'{$column}' => [".implode(', ', $rules).']';
    // }

    // protected function generateRule(string $column, array $info): array
    // {
    //     $type = $this->getColumnType($column, $info);
    //     $rules = ['required'];
    //     return array_merge($rules, array_filter($type['validation']));
    // }
}
