<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

abstract class BaseGenerator
{
    protected string $stubPath;
    protected array $replacements = [];

    public function __construct()
    {
        $this->stubPath = __DIR__ . '/../../stubs/';
    }

    protected function getStub(string $type): string
    {
        return File::get($this->stubPath . $type . '.stub');
    }

    protected function makeDirectory(string $path): void
    {
        if (! File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    protected function generateClass(string $stub, array $replacements): string
    {
        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->getStub($stub)
        );
    }

    protected function put(string $path, string $content): void
    {
        $this->makeDirectory(dirname($path));
        File::put($path, $content);
    }

    protected function studlyCase(string $value): string
    {
        return Str::studly($value);
    }

    protected function camelCase(string $value): string
    {
        return Str::camel($value);
    }
}
