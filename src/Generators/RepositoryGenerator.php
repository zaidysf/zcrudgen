<?php

namespace ZaidYasyaf\Zcrudgen\Generators;

class RepositoryGenerator extends BaseGenerator
{
    public function generate(string $name): array
    {
        $basePath = config('zcrudgen.paths.repository', app_path('Repositories'));
        $className = $this->studlyCase($name);

        // Generate Interface
        $interfacePath = $this->generateInterface($basePath, $className);

        // Generate Repository
        $repositoryPath = $this->generateRepository($basePath, $className);

        return [
            'interface' => $interfacePath,
            'repository' => $repositoryPath,
        ];
    }

    protected function generateInterface(string $basePath, string $className): string
    {
        $replacements = [
            '{{ namespace }}' => config('zcrudgen.namespace').'\\Repositories\\Interfaces',
            '{{ class }}' => $className,
        ];

        $content = $this->generateClass('repository.interface', $replacements);
        $path = $basePath.'/Interfaces/'.$className.'RepositoryInterface.php';

        $this->put($path, $content);

        return $path;
    }

    protected function generateRepository(string $basePath, string $className): string
    {
        $replacements = [
            '{{ namespace }}' => config('zcrudgen.namespace').'\\Repositories',
            '{{ class }}' => $className,
            '{{ model_namespace }}' => config('zcrudgen.namespace').'\\Models\\'.$className,
        ];

        $content = $this->generateClass('repository.class', $replacements);
        $path = $basePath.'/'.$className.'Repository.php';

        $this->put($path, $content);

        return $path;
    }
}
