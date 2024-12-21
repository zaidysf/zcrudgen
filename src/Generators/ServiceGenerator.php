<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

class ServiceGenerator extends BaseGenerator
{
    /**
     * Generate the service class
     */
    public function generate(string $name, array $columns): string
    {
        $servicePath = config('zcrudgen.paths.service', app_path('Services'));
        $className = $this->studlyCase($name);

        // Get AI-generated logic if enabled
        $aiLogic = [];
        if (config('zcrudgen.ai.enabled', false)) {
            $aiGenerator = new AiGenerator();
            $aiLogic = $aiGenerator->generateBusinessLogic($name, $columns);
        }

        $replacements = [
            '{{ namespace }}' => config('zcrudgen.namespace') . '\\Services',
            '{{ class }}' => $className,
            '{{ repository_interface }}' => config('zcrudgen.namespace') . '\\Repositories\\Interfaces\\' . $className . 'RepositoryInterface',
            '{{ traits }}' => $this->generateTraits($aiLogic['traits'] ?? []),
            '{{ methods }}' => $this->generateMethods($aiLogic['methods'] ?? []),
            '{{ imports }}' => $this->generateImports($aiLogic['imports'] ?? [], $className),
        ];

        $content = $this->generateClass('service', $replacements);
        $path = $servicePath . '/' . $className . 'Service.php';

        $this->makeDirectory(dirname($path));
        $this->put($path, $content);

        // Generate events if AI is enabled and events are provided
        if (config('zcrudgen.ai.enabled', false) && ! empty($aiLogic['events'])) {
            $this->generateEvents($name, $aiLogic['events']);
        }

        return $path;
    }

    /**
     * Generate trait imports and use statements
     */
    protected function generateTraits(array $traits): string
    {
        if (empty($traits)) {
            return '';
        }

        return 'use ' . implode(', ', array_map(function ($trait) {
            return '\\' . ltrim($trait, '\\');
        }, $traits)) . ';';
    }

    /**
     * Generate method implementations
     */
    protected function generateMethods(array $methods): string
    {
        if (empty($methods)) {
            return $this->getDefaultMethods();
        }

        $methodsCode = [];
        foreach ($methods as $name => $method) {
            $methodsCode[] = $this->formatMethod($name, $method);
        }

        return implode("\n\n", $methodsCode);
    }

    /**
     * Format a single method with documentation
     */
    protected function formatMethod(string $name, array $method): string
    {
        return <<<PHP
        /**
         * {$method['description']}
         */
        {$method['code']}
        PHP;
    }

    /**
     * Generate default CRUD methods
     */
    protected function getDefaultMethods(): string
    {
        return <<<'PHP'
    /**
     * Get all records with optional filters
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(array $filters = []): Collection
    {
        return $this->repository->all($filters);
    }

    /**
     * Find a specific record by ID
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function find(int $id): Model
    {
        return $this->repository->find($id);
    }

    /**
     * Create a new record
     *
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data): Model
    {
        return $this->repository->create($data);
    }

    /**
     * Update an existing record
     *
     * @param int $id
     * @param array $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function update(int $id, array $data): Model
    {
        return $this->repository->update($id, $data);
    }

    /**
     * Delete a record
     *
     * @param int $id
     * @return bool
     */
    public function delete(int $id): bool
    {
        return $this->repository->delete($id);
    }
PHP;
    }

    /**
     * Generate events classes
     */
    protected function generateEvents(string $name, array $events): void
    {
        if (empty($events)) {
            return;
        }

        $eventPath = config('zcrudgen.paths.event', app_path('Events'));
        $className = $this->studlyCase($name);
        $modelVariable = $this->getModelVariable($className);

        foreach ($events as $eventName) {
            $eventClassName = $this->studlyCase($eventName);

            $replacements = [
                '{{ namespace }}' => config('zcrudgen.namespace') . '\\Events',
                '{{ class }}' => $eventClassName,
                '{{ model }}' => $className,
                '{{ model_namespace }}' => config('zcrudgen.namespace') . '\\Models\\' . $className,
                '{{ model_variable }}' => $modelVariable,
            ];

            $content = $this->generateClass('event', $replacements);
            $path = $eventPath . '/' . $eventClassName . '.php';

            $this->makeDirectory(dirname($path));
            $this->put($path, $content);
        }
    }

    /**
     * Generate import statements
     */
    protected function generateImports(array $imports, string $className): string
    {
        $defaultImports = [
            "use {$this->getNamespace()}\\Repositories\\Interfaces\\{$className}RepositoryInterface;",
            'use Illuminate\Database\Eloquent\Collection;',
            'use Illuminate\Database\Eloquent\Model;',
        ];

        if (! empty($imports)) {
            $imports = array_map(function ($import) {
                return 'use ' . ltrim($import, '\\') . ';';
            }, $imports);

            return implode("\n", array_unique(array_merge($defaultImports, $imports)));
        }

        return implode("\n", $defaultImports);
    }

    /**
     * Get model variable name
     */
    protected function getModelVariable(string $className): string
    {
        return lcfirst($className);
    }

    /**
     * Get the configured namespace
     */
    protected function getNamespace(): string
    {
        return rtrim(config('zcrudgen.namespace', 'App'), '\\');
    }
}
