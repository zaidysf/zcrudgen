<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

abstract class BaseGenerator
{
    protected array $replacements = [];

    protected string $stubPath;

    public function __construct()
    {
        $this->stubPath = __DIR__.'/../../stubs/';
    }

    protected function studlyCase(string $value): string
    {
        return Str::studly($value);
    }

    protected function getStub(string $type): string
    {
        return File::get($this->stubPath.$type.'.stub');
    }

    protected function camelCase(string $value): string
    {
        return Str::camel($value);
    }

    protected function getBasePath(): string
    {
        return app()->basePath();
    }

    protected function makeDirectory(string $path): void
    {
        if (! is_dir($path)) {
            mkdir($path, 0777, true);
        }
    }

    protected function put(string $path, string $content): void
    {
        // Ensure the path is absolute
        if (! str_starts_with($path, '/')) {
            $path = $this->getBasePath().'/'.$path;
        }

        $this->makeDirectory(dirname($path));
        file_put_contents($path, $content);
    }

    protected function generateClass(string $type, array $replacements): string
    {
        $stubPath = __DIR__.'/../../stubs/'.$type.'.stub';

        if (! file_exists($stubPath)) {
            throw new \RuntimeException("Stub file not found: {$type}.stub");
        }

        $stub = file_get_contents($stubPath);

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $stub
        );
    }

    protected function getColumnInfo(string $tableName, string $column): array
    {
        // Check if the table exists
        if (! Schema::hasTable($tableName)) {
            return $this->getDefaultColumnInfo($column);
        }

        // Check if the column exists
        if (! Schema::hasColumn($tableName, $column)) {
            return $this->getDefaultColumnInfo($column);
        }

        // Get column details
        $columnData = Schema::getConnection()
            ->select("SHOW FULL COLUMNS FROM `$tableName` WHERE Field = ?", [$column]);

        if (empty($columnData)) {
            return $this->getDefaultColumnInfo($column);
        }

        $columnDetails = $columnData[0];

        return [
            'type' => $this->extractType($columnDetails->Type), // E.g., varchar, int, date
            'length' => $this->extractLengthFromType($columnDetails->Type), // E.g., 255 for varchar(255)
            'precision' => $this->extractPrecisionFromType($columnDetails->Type), // For decimal types
            'scale' => $this->extractScaleFromType($columnDetails->Type),         // For decimal types
            'unsigned' => str_contains($columnDetails->Type, 'unsigned'),
            'fixed' => $this->isFixedType($columnDetails->Type), // For fixed-length types like char
            'nullable' => $columnDetails->Null === 'YES',
            'default' => $columnDetails->Default,
            'autoincrement' => $columnDetails->Extra === 'auto_increment',
        ];
    }

    protected function extractType(string $type): string
    {
        // Extract the base type (e.g., varchar, int, date)
        if (preg_match('/^(\w+)/', $type, $matches)) {
            return $matches[1];
        }

        return 'unknown';
    }

    protected function extractLengthFromType(string $type): ?int
    {
        // Extract length (e.g., 255 from varchar(255))
        if (preg_match('/\((\d+)\)/', $type, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    protected function extractPrecisionFromType(string $type): ?int
    {
        // Extract precision for decimal types (e.g., 10 from decimal(10,2))
        if (preg_match('/\((\d+),\d+\)/', $type, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    protected function extractScaleFromType(string $type): ?int
    {
        // Extract scale for decimal types (e.g., 2 from decimal(10,2))
        if (preg_match('/\(\d+,(\d+)\)/', $type, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    protected function isFixedType(string $type): bool
    {
        // Check if the type is fixed-length (e.g., char)
        return str_starts_with($type, 'char');
    }

    protected function getColumnDefinition(string $column, array $columnInfo): array
    {
        $type = $columnInfo['type'];

        return match ($type) {
            'bigint', 'integer', 'smallint' => [
                'cast' => 'integer',
                'validation' => ['integer'],
                'openapi' => [
                    'type' => 'integer',
                    'format' => $type === 'bigint' ? 'int64' : 'int32',
                ],
            ],
            'decimal' => [
                'cast' => "decimal:{$columnInfo['scale']}",
                'validation' => ['numeric', "decimal:{$columnInfo['scale']}"],
                'openapi' => [
                    'type' => 'number',
                    'format' => 'decimal',
                    'precision' => $columnInfo['precision'],
                    'scale' => $columnInfo['scale'],
                ],
            ],
            'float', 'double' => [
                'cast' => 'float',
                'validation' => ['numeric'],
                'openapi' => [
                    'type' => 'number',
                    'format' => 'float',
                ],
            ],
            'boolean' => [
                'cast' => 'boolean',
                'validation' => ['boolean'],
                'openapi' => ['type' => 'boolean'],
            ],
            'datetime', 'timestamp' => [
                'cast' => 'datetime',
                'validation' => ['date_format:Y-m-d H:i:s'],
                'openapi' => [
                    'type' => 'string',
                    'format' => 'date-time',
                ],
            ],
            'date' => [
                'cast' => 'date',
                'validation' => ['date'],
                'openapi' => [
                    'type' => 'string',
                    'format' => 'date',
                ],
            ],
            'time' => [
                'cast' => 'datetime',
                'validation' => ['date_format:H:i:s'],
                'openapi' => [
                    'type' => 'string',
                    'format' => 'time',
                ],
            ],
            'json' => [
                'cast' => 'array',
                'validation' => ['array'],
                'openapi' => [
                    'type' => 'object',
                ],
            ],
            'text', 'longtext', 'mediumtext' => [
                'cast' => null,
                'validation' => ['string'],
                'openapi' => [
                    'type' => 'string',
                    'format' => 'text',
                ],
            ],
            default => [
                'cast' => null,
                'validation' => $this->getDefaultValidation($columnInfo),
                'openapi' => [
                    'type' => 'string',
                    'maxLength' => $columnInfo['length'] ?? null,
                ],
            ]
        };
    }

    protected function getDefaultValidation(array $columnInfo): array
    {
        $rules = ['string'];

        if (isset($columnInfo['length'])) {
            $rules[] = "max:{$columnInfo['length']}";
        }

        return $rules;
    }

    protected function getDefaultColumnInfo(string $column): array
    {
        return match ($column) {
            'id' => [
                'type' => 'integer',
                'autoincrement' => true,
                'nullable' => false,
                'unsigned' => true,
            ],
            'created_at', 'updated_at', 'deleted_at' => [
                'type' => 'datetime',
                'nullable' => true,
                'unsigned' => false,
            ],
            default => [
                'type' => 'string',
                'length' => 255,
                'nullable' => false,
                'unsigned' => false,
            ]
        };
    }
}
