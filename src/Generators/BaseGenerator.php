<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Facades\File;
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
}
