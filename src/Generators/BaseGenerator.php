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
        if (! Schema::hasTable($tableName)) {
            return $this->getDefaultColumnInfo($column);
        }

        // Use the Doctrine Schema Manager to get column information
        $schemaManager = Schema::getConnection()->getDoctrineSchemaManager();

        // Ensure the table name is in the correct format for the schema manager
        $tableDetails = $schemaManager->listTableDetails($tableName);

        if (! $tableDetails->hasColumn($column)) {
            return $this->getDefaultColumnInfo($column);
        }

        $doctrineColumn = $tableDetails->getColumn($column);

        return [
            'type' => $doctrineColumn->getType()->getName(),
            'length' => $doctrineColumn->getLength(),
            'precision' => $doctrineColumn->getPrecision(),
            'scale' => $doctrineColumn->getScale(),
            'unsigned' => $doctrineColumn->getUnsigned(),
            'fixed' => $doctrineColumn->getFixed(),
            'nullable' => ! $doctrineColumn->getNotnull(),
            'default' => $doctrineColumn->getDefault(),
            'autoincrement' => $doctrineColumn->getAutoincrement(),
        ];
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
