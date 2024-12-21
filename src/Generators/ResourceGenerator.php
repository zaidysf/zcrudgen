<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

class ResourceGenerator extends BaseGenerator
{
    public function generate(string $name, array $columns): string
    {
        $resourcePath = config('zcrudgen.paths.resource', app_path('Http/Resources'));
        $className = $this->studlyCase($name);

        $replacements = [
            '{{ namespace }}' => config('zcrudgen.namespace').'\\Http\\Resources',
            '{{ class }}' => $className,
            '{{ attributes }}' => $this->generateAttributes($columns),
        ];

        $content = $this->generateClass('resource', $replacements);
        $path = $resourcePath.'/'.$className.'Resource.php';

        $this->put($path, $content);

        return $path;
    }

    protected function generateAttributes(array $columns): string
    {
        $attributes = array_map(function ($column) {
            return "            '{$column}' => \$this->{$column}";
        }, array_filter($columns, function ($column) {
            return ! in_array($column, ['password']);
        }));

        return implode(",\n", $attributes);
    }
}
