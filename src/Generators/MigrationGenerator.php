<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MigrationGenerator extends BaseGenerator
{
    public function generate(string $name, array $columns = []): string
    {
        $tableName = Str::plural(Str::snake($name));
        $migrationPath = database_path('migrations');

        // Ensure migrations directory exists
        if (! File::isDirectory($migrationPath)) {
            File::makeDirectory($migrationPath, 0777, true);
        }

        // Check if migration already exists
        if ($existingPath = $this->migrationExists($tableName)) {
            return $existingPath;
        }

        $timestamp = date('Y_m_d_His');
        $filename = $timestamp . "_create_{$tableName}_table.php";
        $path = $migrationPath . '/' . $filename;

        $replacements = [
            '{{ class }}' => 'Create' . Str::studly($tableName) . 'Table',
            '{{ table }}' => $tableName,
            '{{ schema }}' => $this->generateSchema($columns),
        ];

        $content = $this->generateClass('migration', $replacements);

        File::put($path, $content);

        return $path;
    }

    protected function migrationExists(string $tableName): ?string
    {
        $pattern = database_path("migrations/*_create_{$tableName}_table.php");
        $files = glob($pattern);

        return $files ? $files[0] : null;
    }

    protected function generateSchema(array $columns): string
    {
        if (empty($columns)) {
            return $this->getDefaultSchema();
        }

        $schema = [];
        foreach ($columns as $column) {
            $columnDefinition = $this->generateColumn($column);
            if ($columnDefinition) {
                $schema[] = $columnDefinition;
            }
        }

        return implode("\n            ", array_filter($schema));
    }

    protected function generateColumn(string $column): ?string
    {
        if ($column === 'id') {
            return '$table->id();';
        }

        if (in_array($column, ['created_at', 'updated_at'])) {
            return '$table->timestamps();';
        }

        if ($column === 'deleted_at') {
            return '$table->softDeletes();';
        }

        if (str_ends_with($column, '_id')) {
            $reference = str_replace('_id', '', $column);

            return "\$table->foreignId('{$column}')->constrained('" . Str::plural($reference) . "');";
        }

        return match (true) {
            str_contains($column, 'email') => "\$table->string('{$column}')->unique();",
            str_contains($column, 'password') => "\$table->string('{$column}');",
            str_contains($column, 'description') => "\$table->text('{$column}')->nullable();",
            str_contains($column, 'is_') => "\$table->boolean('{$column}')->default(false);",
            str_contains($column, 'price') => "\$table->decimal('{$column}', 10, 2);",
            str_contains($column, 'amount') => "\$table->decimal('{$column}', 10, 2);",
            str_contains($column, 'quantity') => "\$table->integer('{$column}')->default(0);",
            str_contains($column, 'stock') => "\$table->integer('{$column}')->default(0);",
            default => "\$table->string('{$column}');"
        };
    }

    protected function getDefaultSchema(): string
    {
        return <<<'PHP'
$table->id();
            $table->string('name');
            $table->timestamps();
PHP;
    }
}
