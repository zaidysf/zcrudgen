<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

use Illuminate\Support\Str;

class TestGenerator extends BaseGenerator
{
    public function generate(string $name, array $columns): string
    {
        $testPath = base_path('tests/Feature/Api');
        $className = $this->studlyCase($name);

        $replacements = [
            '{{ namespace }}' => 'Tests\\Feature\\Api',
            '{{ model_namespace }}' => config('zcrudgen.namespace').'\\Models\\'.$className,
            '{{ class }}' => $className,
            '{{ model }}' => $className,
            '{{ model_plural_lower }}' => Str::plural(Str::lower($name)),
            '{{ model_singular_lower }}' => Str::lower($name),
            '{{ route_prefix }}' => Str::plural(Str::kebab($name)),
            '{{ table }}' => Str::plural(Str::snake($name)),
            '{{ model_variable }}' => Str::camel($name),
            '{{ json_structure }}' => $this->generateJsonStructure($columns),
            '{{ database_assertions }}' => $this->generateDatabaseAssertions($columns),
        ];

        $content = $this->generateClass('test.feature', $replacements);
        $path = $testPath.'/'.$className.'ControllerTest.php';

        $this->makeDirectory(dirname($path));
        $this->put($path, $content);

        // Generate Factory if it doesn't exist
        $this->generateFactory($name, $columns);

        return $path;
    }

    protected function generateJsonStructure(array $columns): string
    {
        $structure = array_filter($columns, function ($column) {
            return ! in_array($column, ['password']);
        });

        return "'".implode("',\n                        '", $structure)."'";
    }

    protected function generateDatabaseAssertions(array $columns): string
    {
        $assertions = [];
        foreach ($columns as $column) {
            if (in_array($column, ['id', 'created_at', 'updated_at', 'password'])) {
                continue;
            }
            $assertions[] = "'$column' => \$data['$column']";
        }

        return implode(",\n            ", $assertions);
    }

    protected function generateFactory(string $name, array $columns): void
    {
        $factoryPath = database_path('factories');
        $className = $this->studlyCase($name);

        $replacements = [
            '{{ namespace }}' => config('zcrudgen.namespace').'\\Database\\Factories',
            '{{ class }}' => $className,
            '{{ model_namespace }}' => config('zcrudgen.namespace').'\\Models\\'.$className,
            '{{ factory_definition }}' => $this->generateFactoryDefinition($columns),
        ];

        $content = $this->generateClass('factory', $replacements);
        $path = $factoryPath.'/'.$className.'Factory.php';

        if (! file_exists($path)) {
            $this->makeDirectory(dirname($path));
            $this->put($path, $content);
        }
    }

    protected function generateFactoryDefinition(array $columns): string
    {
        $definitions = [];
        foreach ($columns as $column) {
            if (in_array($column, ['id', 'created_at', 'updated_at'])) {
                continue;
            }

            $definitions[] = $this->getFactoryDefinitionForColumn($column);
        }

        return implode(",\n            ", $definitions);
    }

    protected function getFactoryDefinitionForColumn(string $column): string
    {
        if (str_ends_with($column, '_id')) {
            $related = Str::studly(str_replace('_id', '', $column));

            return "'$column' => \\{$related}::factory()";
        }

        return match (true) {
            $column === 'email' => "'$column' => fake()->unique()->safeEmail()",
            $column === 'name' => "'$column' => fake()->name()",
            $column === 'password' => "'$column' => bcrypt('password')",
            str_contains($column, 'description') => "'$column' => fake()->paragraph()",
            str_contains($column, 'title') => "'$column' => fake()->sentence()",
            str_contains($column, 'price') => "'$column' => fake()->randomFloat(2, 10, 1000)",
            str_contains($column, 'quantity') || str_contains($column, 'stock') => "'$column' => fake()->numberBetween(0, 100)",
            str_contains($column, 'is_') || str_contains($column, 'has_') => "'$column' => fake()->boolean()",
            str_contains($column, 'date') => "'$column' => fake()->dateTime()",
            str_contains($column, 'phone') => "'$column' => fake()->phoneNumber()",
            str_contains($column, 'address') => "'$column' => fake()->address()",
            default => "'$column' => fake()->word()",
        };
    }
}
